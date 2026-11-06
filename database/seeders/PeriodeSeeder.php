<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Periode;

class PeriodeSeeder extends Seeder
{
    public function run(): void
    {
        Periode::updateOrCreate(
            ['kode' => 'GASAL-2026'],
            [
                'nama'            => 'Semester Gasal 2026/2027',
                'tanggal_mulai'   => '2026-09-01',
                'tanggal_selesai' => '2027-01-31',
                'status'          => 'aktif',
            ]
        );

        Periode::updateOrCreate(
            ['kode' => 'GENAP-2026'],
            [
                'nama'            => 'Semester Genap 2025/2026',
                'tanggal_mulai'   => '2026-02-01',
                'tanggal_selesai' => '2026-07-31',
                'status'          => 'selesai',
            ]
        );
    }
}
