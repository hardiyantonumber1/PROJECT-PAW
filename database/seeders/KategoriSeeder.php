<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;
use App\Models\Unit;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $unitMap = Unit::pluck('id', 'nama');

        $data = [
            [
                'nama'              => 'Kekerasan Seksual / Perundungan',
                'batas_hari'        => 3,
                'tingkat_keparahan' => 'kritis',
                'sla_respons_jam'   => 4,
                'sla_selesai_jam'   => 72,
                'unit_default_id'   => null,
            ],
            [
                'nama'              => 'Fasilitas & Sarana',
                'batas_hari'        => 14,
                'tingkat_keparahan' => 'tinggi',
                'sla_respons_jam'   => 24,
                'sla_selesai_jam'   => 120,
                'unit_default_id'   => $unitMap['Sarana dan Prasarana'] ?? null,
            ],
            [
                'nama'              => 'Akademik & Kurikulum',
                'batas_hari'        => 7,
                'tingkat_keparahan' => 'sedang',
                'sla_respons_jam'   => 48,
                'sla_selesai_jam'   => 168,
                'unit_default_id'   => $unitMap['Akademik'] ?? null,
            ],
            [
                'nama'              => 'Kemahasiswaan & Organisasi',
                'batas_hari'        => 7,
                'tingkat_keparahan' => 'sedang',
                'sla_respons_jam'   => 48,
                'sla_selesai_jam'   => 168,
                'unit_default_id'   => $unitMap['Kemahasiswaan'] ?? null,
            ],
            [
                'nama'              => 'Administrasi & Layanan',
                'batas_hari'        => 10,
                'tingkat_keparahan' => 'rendah',
                'sla_respons_jam'   => 72,
                'sla_selesai_jam'   => 240,
                'unit_default_id'   => $unitMap['Administrasi'] ?? null,
            ],
            [
                'nama'              => 'Jaringan & Infrastruktur IT',
                'batas_hari'        => 7,
                'tingkat_keparahan' => 'tinggi',
                'sla_respons_jam'   => 24,
                'sla_selesai_jam'   => 96,
                'unit_default_id'   => $unitMap['Lab dan Jaringan'] ?? null,
            ],
            [
                'nama'              => 'Lainnya',
                'batas_hari'        => 10,
                'tingkat_keparahan' => 'rendah',
                'sla_respons_jam'   => 72,
                'sla_selesai_jam'   => 240,
                'unit_default_id'   => null,
            ],
        ];

        foreach ($data as $d) {
            Kategori::updateOrCreate(['nama' => $d['nama']], $d);
        }
    }
}
