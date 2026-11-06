<?php

namespace App\Http\Controllers;

use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use App\Models\Kategori;
use App\Models\Unit;
use App\Support\EksporCsv;
use App\Support\Ringkasan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapController extends Controller
{
    public function index(Request $request): View
    {
        [$dari, $sampai] = $this->periode($request);

        $dasar = Aspirasi::query()->whereBetween('tanggal_masuk', [$dari, $sampai]);

        $total = (clone $dasar)->count();
        $selesai = (clone $dasar)->where('status', StatusAspirasi::Selesai->value)->count();
        $terlambat = (clone $dasar)->terlambat()->count();

        $persenSelesai = $total > 0 ? (int) round($selesai / $total * 100) : 0;
        $persenTerlambat = $total > 0 ? (int) round($terlambat / $total * 100) : 0;

        $hitung = Ringkasan::hitungStatus($dasar);
        $donut = Ringkasan::donut($hitung, $total);

        $rataHari = Ringkasan::rataHari(
            (clone $dasar)->whereNotNull('selesai_pada')->get(['tanggal_masuk', 'selesai_pada'])
        );

        $semuaTerlambat = (clone $dasar)->terlambat()->get(['tanggal_masuk', 'tenggat', 'status']);
        $rataTerlambat = $semuaTerlambat->isEmpty()
            ? 0.0
            : round($semuaTerlambat->avg(fn (Aspirasi $a) => abs($a->hari_tersisa)), 1);

        $perKategori = $this->rincian(
            Kategori::orderBy('nama')->pluck('nama', 'id'),
            (clone $dasar)->selectRaw('kategori_id, count(*) as jumlah')->groupBy('kategori_id')->pluck('jumlah', 'kategori_id'),
            $total
        );

        $perUnit = $this->rincian(
            Unit::orderBy('nama')->pluck('nama', 'id'),
            (clone $dasar)->selectRaw('unit_id, count(*) as jumlah')->groupBy('unit_id')->pluck('jumlah', 'unit_id'),
            $total
        );

        $daftarTerlambat = (clone $dasar)
            ->terlambat()
            ->with(['kategori', 'unit'])
            ->orderBy('tenggat')
            ->limit(8)
            ->get();

        $unitDominan = $perUnit->first(fn ($baris) => $baris['jumlah'] > 0);

        return view('admin.rekap', compact(
            'dari', 'sampai', 'total', 'selesai', 'terlambat',
            'persenSelesai', 'persenTerlambat', 'hitung', 'donut',
            'rataHari', 'rataTerlambat', 'perKategori', 'perUnit',
            'daftarTerlambat', 'unitDominan'
        ));
    }

    public function ekspor(Request $request): StreamedResponse
    {
        [$dari, $sampai] = $this->periode($request);

        $daftar = Aspirasi::query()
            ->whereBetween('tanggal_masuk', [$dari, $sampai])
            ->with(['kategori', 'unit', 'pengguna'])
            ->orderBy('tanggal_masuk')
            ->get();

        $baris = $daftar->map(fn (Aspirasi $a) => [
            $a->kode,
            $a->tanggal_masuk->translatedFormat('d/m/Y'),
            $a->judul,
            $a->kategori->nama,
            $a->unit->nama,
            $a->pengguna->nama,
            $a->status->value,
            $a->tenggat->translatedFormat('d/m/Y'),
            $a->selesai_pada?->translatedFormat('d/m/Y') ?? '-',
            $a->terlambat ? 'Ya' : 'Tidak',
        ]);

        $nama = 'rekap-aspirasi-'.$dari->format('Ymd').'-'.$sampai->format('Ymd').'.csv';

        return EksporCsv::unduh($nama, [
            'Kode', 'Tanggal Masuk', 'Judul', 'Kategori', 'Unit', 'Pelapor',
            'Status', 'Tenggat', 'Selesai Pada', 'Terlambat',
        ], $baris);
    }

    /* ------------------------------------------------------------- bantuan */

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periode(Request $request): array
    {
        $dari = $this->tanggal($request->query('dari'), now()->startOfMonth());
        $sampai = $this->tanggal($request->query('sampai'), now());

        if ($sampai->lt($dari)) {
            [$dari, $sampai] = [$sampai->copy()->startOfDay(), $dari->copy()->endOfDay()];
        }

        return [$dari->startOfDay(), $sampai->endOfDay()];
    }

    private function tanggal($nilai, Carbon $bawaan): Carbon
    {
        if (blank($nilai)) {
            return $bawaan->copy();
        }

        try {
            return Carbon::parse($nilai);
        } catch (\Exception) {
            return $bawaan->copy();
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $nama
     * @param  \Illuminate\Support\Collection<int, int>  $jumlah
     * @return \Illuminate\Support\Collection<int, array{nama: string, jumlah: int, persen: int, kelas: string}>
     */
    private function rincian($nama, $jumlah, int $total)
    {
        $kelas = ['', 'v', 'a', 'g'];

        return $nama
            ->map(function (string $label, int $id) use ($jumlah, $total) {
                $banyak = (int) ($jumlah[$id] ?? 0);

                return [
                    'nama' => $label,
                    'jumlah' => $banyak,
                    'persen' => $total > 0 ? (int) round($banyak / $total * 100) : 0,
                ];
            })
            ->sortByDesc('jumlah')
            ->values()
            ->map(fn (array $baris, int $i) => $baris + ['kelas' => $kelas[$i % count($kelas)]]);
    }
}
