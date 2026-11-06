<?php

namespace App\Support;

use App\Enums\StatusAspirasi;

/**
 * Perhitungan ringkasan yang dipakai bersama oleh dashboard mahasiswa
 * dan rekapitulasi admin (donut chart, persentase, rata-rata hari).
 */
class Ringkasan
{
    /**
     * Susun nilai CSS conic-gradient dari jumlah tiap status.
     *
     * @param  array<string, int>  $hitung
     */
    public static function donut(array $hitung, int $total): string
    {
        if ($total <= 0) {
            return 'conic-gradient(#EDF1F7 0 100%)';
        }

        $mulai = 0.0;
        $bagian = [];

        foreach ($hitung as $status => $jumlah) {
            if ($jumlah <= 0) {
                continue;
            }

            $selesai = $mulai + ($jumlah / $total * 100);
            $bagian[] = StatusAspirasi::from($status)->warna()
                .' '.round($mulai, 2).'% '.round($selesai, 2).'%';
            $mulai = $selesai;
        }

        return 'conic-gradient('.implode(', ', $bagian).')';
    }

    /**
     * Jumlah aspirasi per status, urut sesuai urutan ENUM.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return array<string, int>
     */
    public static function hitungStatus($query): array
    {
        $hitung = [];

        foreach (StatusAspirasi::cases() as $status) {
            $hitung[$status->value] = (clone $query)->where('status', $status->value)->count();
        }

        return $hitung;
    }

    /**
     * Rata-rata hari penanganan (tanggal masuk → selesai pada).
     *
     * @param  iterable<int, \App\Models\Aspirasi>  $daftar
     */
    public static function rataHari(iterable $daftar): ?float
    {
        $hari = [];

        foreach ($daftar as $a) {
            if ($a->selesai_pada && $a->tanggal_masuk) {
                $hari[] = $a->tanggal_masuk->diffInDays($a->selesai_pada);
            }
        }

        if ($hari === []) {
            return null;
        }

        return round(array_sum($hari) / count($hari), 1);
    }
}
