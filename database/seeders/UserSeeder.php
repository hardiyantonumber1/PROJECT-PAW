<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Pengguna;
use App\Models\Prodi;
use App\Models\Unit;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ti   = Prodi::where('kode', 'TI')->first();
        $si   = Prodi::where('kode', 'SI')->first();
        $unitAkademik = Unit::where('nama', 'Akademik')->first();
        $unitKemahasiswaan = Unit::where('nama', 'Kemahasiswaan')->first();
        $unitSarpras = Unit::where('nama', 'Sarana dan Prasarana')->first();

        $users = [
            [
                'nama'     => 'Super Admin SIAF',
                'email'    => 'superadmin@filkom.ub.ac.id',
                'password' => Hash::make('password'),
                'role'     => 'super_admin',
                'npm_nip'  => '19800101001',
            ],
            [
                'nama'     => 'Admin Prodi TI',
                'email'    => 'adminprodi.ti@filkom.ub.ac.id',
                'password' => Hash::make('password'),
                'role'     => 'admin_prodi',
                'prodi_id' => $ti?->id,
                'npm_nip'  => '19850101002',
            ],
            [
                'nama'     => 'Koordinator Akademik',
                'email'    => 'koordinator@filkom.ub.ac.id',
                'password' => Hash::make('password'),
                'role'     => 'koordinator',
                'unit_id'  => $unitAkademik?->id,
                'npm_nip'  => '19820101003',
            ],
            [
                'nama'     => 'Petugas Kemahasiswaan',
                'email'    => 'petugas.kemahasiswaan@filkom.ub.ac.id',
                'password' => Hash::make('password'),
                'role'     => 'petugas',
                'unit_id'  => $unitKemahasiswaan?->id,
                'npm_nip'  => '19900101004',
            ],
            [
                'nama'     => 'Petugas Sarana Prasarana',
                'email'    => 'petugas.sarpras@filkom.ub.ac.id',
                'password' => Hash::make('password'),
                'role'     => 'petugas',
                'unit_id'  => $unitSarpras?->id,
                'npm_nip'  => '19910101005',
            ],
            [
                'nama'     => 'Tim ULTKSP FILKOM',
                'email'    => 'ultksp@filkom.ub.ac.id',
                'password' => Hash::make('password'),
                'role'     => 'ultksp',
                'npm_nip'  => '19880101006',
            ],
            [
                'nama'     => 'Pimpinan Dekanat',
                'email'    => 'pimpinan@filkom.ub.ac.id',
                'password' => Hash::make('password'),
                'role'     => 'pimpinan',
                'npm_nip'  => '19750101007',
            ],
            [
                'nama'     => 'Mahasiswa Demo TI',
                'email'    => 'mahasiswa@student.ub.ac.id',
                'password' => Hash::make('password'),
                'role'     => 'mahasiswa',
                'prodi_id' => $ti?->id,
                'npm_nip'  => '235150200111001',
            ],
            [
                'nama'     => 'Mahasiswa Demo SI',
                'email'    => 'mahasiswa.si@student.ub.ac.id',
                'password' => Hash::make('password'),
                'role'     => 'mahasiswa',
                'prodi_id' => $si?->id,
                'npm_nip'  => '235150207111001',
            ],
        ];

        foreach ($users as $data) {
            Pengguna::updateOrCreate(['email' => $data['email']], $data);
        }

        // Set koordinator unit
        if ($unitAkademik) {
            $k = Pengguna::where('email', 'koordinator@filkom.ub.ac.id')->first();
            if ($k) $unitAkademik->update(['koordinator_id' => $k->id]);
        }
    }
}
