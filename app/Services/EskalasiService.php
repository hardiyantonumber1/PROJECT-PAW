<?php

namespace App\Services;

use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use App\Models\Eskalasi;
use App\Models\RiwayatPerubahan;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Mesin eskalasi SIAF.
 *
 * Tiga tingkat, dipilih dari aturan yang paling berat:
 *
 *   L1 — Belum ada respons : status masih Diajukan melewati SLA respons kategori.
 *   L2 — Tenggat terlampaui: tanggal sekarang melewati kolom `tenggat`.
 *   L3 — Pembiaran        : melewati tenggat + masa tenggang (½ batas hari, min 2 hari).
 *
 * Sifat yang dijamin:
 *
 *  1. IDEMPOTEN. Level hanya boleh naik (monotonic). Bila scheduler berjalan
 *     berkali-kali, tidak ada baris eskalasi ganda: dijaga oleh kolom
 *     level_eskalasi dan UNIQUE (aspirasi_id, level) di basis data.
 *  2. BERHENTI SAAT DITANGANI. Begitu petugas menulis tindak lanjut,
 *     `ditangani_pada` terisi dan eskalasi ditahan selama masa tenggang
 *     respons — jadi sistem tidak "menembak" unit yang sedang bekerja.
 *  3. BERHENTI SAAT FINAL. Status Selesai/Ditolak tidak pernah dieskalasi.
 *  4. COOLDOWN. Minimal 60 menit antar eskalasi pada aspirasi yang sama,
 *     sehingga menjalankan perintah manual berulang tidak menaikkan level.
 *  5. TERCATAT. Setiap kenaikan menulis satu baris `eskalasi` dan satu baris
 *     `riwayat_perubahan` — jejak audit tidak bisa hilang.
 */
class EskalasiService
{
    public const LEVEL_MAKS = 3;

    /** Jeda minimum antar kenaikan level pada aspirasi yang sama. */
    public const COOLDOWN_MENIT = 60;

    /** Penerima "notifikasi" per level (dipakai untuk kolom penerima_peran). */
    private const PENERIMA = [
        1 => 'koordinator',
        2 => 'pimpinan',
        3 => 'super_admin',
    ];

    /**
     * Pindai seluruh aspirasi berjalan dan naikkan level yang memang layak.
     *
     * @return array{dipindai:int, naik:int, dilewati:int, per_level:array<int,int>, uji:bool}
     */
    public function jalankan(bool $uji = false): array
    {
        $hasil = [
            'dipindai' => 0,
            'naik'     => 0,
            'dilewati' => 0,
            'per_level' => [1 => 0, 2 => 0, 3 => 0],
            'uji'      => $uji,
        ];

        Aspirasi::query()
            ->perluDipindai()
            ->with(['kategori', 'unit'])
            ->chunkById(200, function ($daftar) use (&$hasil, $uji) {
                foreach ($daftar as $aspirasi) {
                    $hasil['dipindai']++;

                    $tujuan = $this->targetLevel($aspirasi);

                    if ($tujuan <= (int) $aspirasi->level_eskalasi) {
                        $hasil['dilewati']++;

                        continue;
                    }

                    if ($this->baruDitangani($aspirasi)) {
                        $hasil['dilewati']++;

                        continue;
                    }

                    if ($this->masihCooldown($aspirasi)) {
                        $hasil['dilewati']++;

                        continue;
                    }

                    if ($uji) {
                        $hasil['naik']++;
                        $hasil['per_level'][$tujuan]++;

                        continue;
                    }

                    if ($this->naikkan($aspirasi, $tujuan)) {
                        $hasil['naik']++;
                        $hasil['per_level'][$tujuan]++;
                    } else {
                        $hasil['dilewati']++;
                    }
                }
            });

        return $hasil;
    }

    /**
     * Level yang seharusnya dimiliki aspirasi saat ini (0 = tidak perlu).
     */
    public function targetLevel(Aspirasi $aspirasi, ?int $sekarang = null): int
    {
        if (! $aspirasi->status->berjalan()) {
            return 0;
        }

        if (! $aspirasi->tanggal_masuk) {
            return 0;
        }

        $sekarang ??= now()->timestamp;

        $kategori = $aspirasi->kategori;
        $batasHari = max(1, (int) ($kategori->batas_hari ?? 7));
        $jamRespons = max(1, (int) ($kategori->sla_respons_jam ?? 24));

        $tenggat = $aspirasi->tenggat ?? $aspirasi->tanggal_masuk->copy()->addDays($batasHari);

        // Masa tenggang setelah tenggat: setengah batas hari, minimum 2 hari.
        $tenggangHari = max(2, (int) ceil($batasHari / 2));
        $batasPembiaran = $tenggat->copy()->addDays($tenggangHari);

        if ($sekarang >= $batasPembiaran->timestamp) {
            return 3;
        }

        if ($sekarang >= $tenggat->timestamp) {
            return 2;
        }

        // L1 hanya relevan selama belum ada respons sama sekali.
        if ($aspirasi->status === StatusAspirasi::Diajukan
            && $sekarang >= $aspirasi->tanggal_masuk->copy()->addHours($jamRespons)->timestamp) {
            return 1;
        }

        return 0;
    }

    /**
     * Naikkan satu aspirasi ke level tertentu. False bila tidak jadi naik
     * (sudah ada di level itu — dijaga unique index).
     */
    public function naikkan(Aspirasi $aspirasi, int $level): bool
    {
        $level = max(1, min(self::LEVEL_MAKS, $level));
        $sebelum = (int) $aspirasi->level_eskalasi;

        if ($level <= $sebelum) {
            return false;
        }

        try {
            DB::transaction(function () use ($aspirasi, $level, $sebelum) {
                // Jaminan anti-dobel tingkat basis data: unique (aspirasi_id, level).
                $sudahAda = Eskalasi::query()
                    ->where('aspirasi_id', $aspirasi->id)
                    ->where('level', $level)
                    ->exists();

                if (! $sudahAda) {
                    Eskalasi::create([
                        'aspirasi_id'      => $aspirasi->id,
                        'level'            => $level,
                        'level_sebelum'    => $sebelum,
                        'alasan'           => Eskalasi::LABEL[$level],
                        'rincian'          => $this->rincian($aspirasi, $level),
                        'penerima_peran'   => self::PENERIMA[$level] ?? 'super_admin',
                        'keterlambatan_jam' => $this->keterlambatanJam($aspirasi),
                        'terjadi_pada'     => now(),
                    ]);
                }

                $aspirasi->forceFill([
                    'level_eskalasi'       => $level,
                    'eskalasi_terakhir_pada' => now(),
                ])->save();

                RiwayatPerubahan::create([
                    'aspirasi_id' => $aspirasi->id,
                    'pengguna_id' => null,
                    'aktor'       => 'Sistem (scheduler)',
                    'aktor_peran' => 'sistem',
                    'entitas'     => 'aspirasi',
                    'entitas_id'  => $aspirasi->id,
                    'aksi'        => 'eskalasi',
                    'field'       => 'level_eskalasi',
                    'nilai_lama'  => (string) $sebelum,
                    'nilai_baru'  => (string) $level,
                    'keterangan'  => Eskalasi::LABEL[$level].' — '.$this->rincian($aspirasi, $level),
                    'ip'          => null,
                    'terjadi_pada' => now(),
                ]);
            });
        } catch (Throwable $e) {
            // Balapan dua proses: unique index menolak baris kedua. Bukan galat.
            report($e);

            return false;
        }

        return true;
    }

    /**
     * Apakah petugas baru saja merespons? Bila ya, tahan eskalasi selama
     * masa tenggang respons kategori supaya unit yang sedang bekerja tidak
     * langsung dinaikkan levelnya.
     */
    public function baruDitangani(Aspirasi $aspirasi): bool
    {
        if (! $aspirasi->ditangani_pada) {
            return false;
        }

        $jamRespons = max(1, (int) ($aspirasi->kategori->sla_respons_jam ?? 24));

        return $this->jamSejak($aspirasi->ditangani_pada) < $jamRespons;
    }

    /** Minimal 60 menit sejak eskalasi terakhir. */
    public function masihCooldown(Aspirasi $aspirasi): bool
    {
        if (! $aspirasi->eskalasi_terakhir_pada) {
            return false;
        }

        return $this->jamSejak($aspirasi->eskalasi_terakhir_pada) < (self::COOLDOWN_MENIT / 60);
    }

    private function jamSejak(?\Illuminate\Support\Carbon $waktu): float
    {
        return $waktu ? (now()->timestamp - $waktu->timestamp) / 3600 : INF;
    }

    private function keterlambatanJam(Aspirasi $aspirasi): int
    {
        if (! $aspirasi->tenggat) {
            return 0;
        }

        return max(0, (int) round((now()->timestamp - $aspirasi->tenggat->timestamp) / 3600));
    }

    private function rincian(Aspirasi $aspirasi, int $level): string
    {
        $kategori = $aspirasi->kategori?->nama ?? 'tanpa kategori';
        $unit = $aspirasi->unit?->nama ?? 'tanpa unit';
        $jam = $this->keterlambatanJam($aspirasi);

        return match ($level) {
            1 => 'Belum ada verifikasi petugas setelah '.($aspirasi->kategori->sla_respons_jam ?? 24)
                .' jam sejak diajukan. Unit '.$unit.' ('.$kategori.') diminta merespons.',
            2 => 'Tenggat penanganan terlewati '.$jam.' jam. Kategori '.$kategori
                .' menetapkan batas '.($aspirasi->kategori->batas_hari ?? 7).' hari.',
            default => 'Tenggat terlewati '.$jam.' jam tanpa penyelesaian — dieskalasi ke pimpinan dekanat.',
        };
    }
}
