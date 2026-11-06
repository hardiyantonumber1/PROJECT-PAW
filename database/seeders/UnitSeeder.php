<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            'Akademik',
            'Kemahasiswaan',
            'Sarana dan Prasarana',
            'Administrasi',
            'Lab dan Jaringan',
        ];

        foreach ($units as $nama) {
            Unit::updateOrCreate(['nama' => $nama]);
        }
    }
}
