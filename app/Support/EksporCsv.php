<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ekspor sederhana ke CSV (pemisah titik koma agar rapi di Excel Indonesia)
 * dengan BOM UTF-8 supaya huruf beraksen tidak rusak.
 */
class EksporCsv
{
    /**
     * @param  array<int, string>  $kepala
     * @param  iterable<int, array<int, mixed>>  $baris
     */
    public static function unduh(string $nama, array $kepala, iterable $baris): StreamedResponse
    {
        return response()->streamDownload(function () use ($kepala, $baris) {
            $keluaran = fopen('php://output', 'w');

            fwrite($keluaran, "\xEF\xBB\xBF");
            fputcsv($keluaran, $kepala, ';');

            foreach ($baris as $b) {
                fputcsv($keluaran, $b, ';');
            }

            fclose($keluaran);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
