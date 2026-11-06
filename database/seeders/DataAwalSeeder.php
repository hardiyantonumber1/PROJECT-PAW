<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Kategori;
use App\Models\Pengguna;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data awal untuk pemakaian nyata (bukan demo).
 *
 * Hanya mengisi data master (unit + kategori sesuai rancangan) dan satu akun admin.
 * Tidak ada aspirasi, tidak ada akun mahasiswa/petugas contoh — akun berikutnya
 * dibuat sendiri oleh admin lewat menu "Kelola Pengguna".
 *
 * Pakai: php artisan migrate:fresh --seed --class=DataAwalSeeder
 *
 * Kata sandi admin awal: "password" — WAJIB diganti lewat menu Kelola Pengguna
 * setelah login pertama.
 */
class DataAwalSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['BAAK', 'BAU', 'Program Studi Informatika'] as $nama) {
            Unit::updateOrCreate(['nama' => $nama]);
        }

        foreach (['Akademik' => 7, 'Fasilitas' => 14, 'Pelayanan' => 5, 'Keuangan' => 10] as $nama => $batas) {
            Kategori::updateOrCreate(['nama' => $nama], ['batas_hari' => $batas]);
        }

        Pengguna::updateOrCreate(
            ['email' => 'admin@kampus.ac.id'],
            [
                'nama' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'unit_id' => null,
            ],
        );

        $this->command?->info('Data awal dibuat: 3 unit, 4 kategori, 1 akun admin (admin@kampus.ac.id).');
    }
}
