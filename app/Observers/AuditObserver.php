<?php

namespace App\Observers;

use App\Models\Aspirasi;
use App\Models\RiwayatPerubahan;
use App\Models\TindakLanjut;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Perekam jejak audit otomatis.
 *
 * Dipasang pada Aspirasi, TindakLanjut, Kategori, Unit, dan Pengguna. Setiap
 * perubahan kolom dicatat satu baris di `riwayat_perubahan`, lengkap dengan
 * nilai lama dan nilai baru sehingga perubahan bisa direkonstruksi.
 *
 * Kolom yang tidak dicatat:
 *  - password / remember_token  → jangan pernah menyimpan rahasia;
 *  - created_at / updated_at    → derau teknis;
 *  - level_eskalasi dkk.        → sudah dicatat EskalasiService dengan alasannya.
 */
class AuditObserver
{
    private const DIABAIKAN = [
        'created_at', 'updated_at', 'deleted_at',
        'password', 'remember_token',
        'level_eskalasi', 'eskalasi_terakhir_pada', 'ditangani_pada',
    ];

    private const LABEL_KOLOM = [
        'judul'              => 'Judul',
        'isi'                => 'Uraian',
        'status'             => 'Status',
        'kategori_id'        => 'Kategori',
        'unit_id'            => 'Unit tujuan',
        'prodi_id'           => 'Program studi',
        'pengguna_id'        => 'Pelapor',
        'penanggung_jawab_id' => 'Penanggung jawab',
        'tenggat'            => 'Tenggat',
        'tanggal_masuk'      => 'Tanggal masuk',
        'selesai_pada'       => 'Selesai pada',
        'alasan_penolakan'   => 'Alasan penolakan',
        'is_anonim'          => 'Mode anonim',
        'kode'               => 'Kode tiket',
        'nama'               => 'Nama',
        'batas_hari'         => 'Batas hari',
        'sla_respons_jam'    => 'SLA respons (jam)',
        'sla_selesai_jam'    => 'SLA selesai (jam)',
        'email'              => 'Email',
        'role'               => 'Peran',
        'npm_nip'            => 'NPM/NIP',
        'tanggapan'          => 'Tanggapan',
        'status_awal'        => 'Status awal',
        'status_akhir'       => 'Status akhir',
        'bukti'              => 'Bukti',
    ];

    public function created(Model $model): void
    {
        $this->catat($model, $model instanceof TindakLanjut ? 'tindak_lanjut' : 'dibuat', null, null, $this->ringkas($model));
    }

    public function updated(Model $model): void
    {
        $asli = $model->getOriginal();

        foreach ($model->getChanges() as $kolom => $baru) {
            if (in_array($kolom, self::DIABAIKAN, true)) {
                continue;
            }

            $lama = $asli[$kolom] ?? null;

            if ($this->sama($lama, $baru)) {
                continue;
            }

            $aksi = match (true) {
                $model instanceof TindakLanjut => 'tindak_lanjut',
                $model instanceof Aspirasi && $kolom === 'status' => 'status',
                default => 'diubah',
            };

            $this->catat($model, $aksi, $kolom, $lama, $baru);
        }
    }

    public function deleted(Model $model): void
    {
        $this->catat($model, 'dihapus', null, $this->ringkas($model), null);
    }

    /* ------------------------------------------------------------------ */

    private function catat(Model $model, string $aksi, ?string $kolom, mixed $lama, mixed $baru): void
    {
        try {
            $user = auth()->user();

            RiwayatPerubahan::create([
                'aspirasi_id' => $this->aspirasiId($model),
                'pengguna_id' => $user?->id,
                'aktor'       => $user?->nama ?? 'Sistem',
                'aktor_peran' => $user?->role?->value ?? 'sistem',
                'entitas'     => $model->getTable(),
                'entitas_id'  => $model->getKey(),
                'aksi'        => $aksi,
                'field'       => $kolom,
                'nilai_lama'  => $this->teks($lama),
                'nilai_baru'  => $this->teks($baru),
                'keterangan'  => $kolom ? (self::LABEL_KOLOM[$kolom] ?? $kolom) : null,
                'ip'          => app()->runningInConsole() ? null : request()->ip(),
                'perangkat'   => null,
                'terjadi_pada' => now(),
            ]);
        } catch (Throwable $e) {
            // Audit tidak boleh menjatuhkan permintaan pengguna.
            Log::warning('Gagal menulis riwayat perubahan: '.$e->getMessage());
        }
    }

    private function aspirasiId(Model $model): ?int
    {
        return match (true) {
            $model instanceof Aspirasi    => $model->getKey(),
            $model instanceof TindakLanjut => $model->aspirasi_id,
            default                       => null,
        };
    }

    private function sama(mixed $lama, mixed $baru): bool
    {
        return $this->teks($lama) === $this->teks($baru);
    }

    private function teks(mixed $nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        // Enum (mis. StatusAspirasi) tidak bisa di-json_encode maupun di-cast
        // langsung ke string — ambil nilai backing-nya.
        if ($nilai instanceof \BackedEnum) {
            return (string) $nilai->value;
        }

        if ($nilai instanceof \UnitEnum) {
            return $nilai->name;
        }

        if ($nilai instanceof \DateTimeInterface) {
            return $nilai->format('Y-m-d H:i:s');
        }

        if (is_bool($nilai)) {
            return $nilai ? 'ya' : 'tidak';
        }

        $teks = is_scalar($nilai) ? (string) $nilai : json_encode($nilai, JSON_UNESCAPED_UNICODE);

        return mb_strlen($teks) > 500 ? mb_substr($teks, 0, 500).'…' : $teks;
    }

    /** Ringkasan singkat untuk aksi dibuat/dihapus. */
    private function ringkas(Model $model): ?string
    {
        return match (true) {
            $model instanceof Aspirasi    => $model->kode.' — '.$model->judul,
            $model instanceof TindakLanjut => 'Status '.$this->teks($model->status_akhir),
            default                       => $model->getAttribute('nama'),
        };
    }
}
