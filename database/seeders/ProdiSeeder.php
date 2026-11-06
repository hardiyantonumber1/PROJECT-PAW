<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prodi;

class ProdiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'TI',  'nama' => 'Teknik Informatika',       'jenjang' => 'S1'],
            ['kode' => 'SI',  'nama' => 'Sistem Informasi',         'jenjang' => 'S1'],
            ['kode' => 'TK',  'nama' => 'Teknik Komputer',          'jenjang' => 'S1'],
            ['kode' => 'PTI', 'nama' => 'Pendidikan Teknologi Informasi', 'jenjang' => 'S1'],
            ['kode' => 'MTI', 'nama' => 'Magister Teknik Informatika', 'jenjang' => 'S2'],
        ];

        foreach ($data as $d) {
            Prodi::updateOrCreate(['kode' => $d['kode']], array_merge($d, ['is_active' => true]));
        }
    }
}
