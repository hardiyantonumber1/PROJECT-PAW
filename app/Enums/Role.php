<?php

namespace App\Enums;

/**
 * Nilai ENUM pengguna.role — 7 peran SIAF FILKOM UB.
 */
enum Role: string
{
    case Mahasiswa   = 'mahasiswa';
    case Petugas     = 'petugas';
    case Koordinator = 'koordinator';
    case AdminProdi  = 'admin_prodi';
    case SuperAdmin  = 'super_admin';
    case Ultksp      = 'ultksp';
    case Pimpinan    = 'pimpinan';

    // Alias untuk kompatibilitas backwards (controller lama pakai Role::Admin)
    public static function Admin(): self { return self::SuperAdmin; }

    public function label(): string
    {
        return match ($this) {
            self::Mahasiswa   => 'Mahasiswa',
            self::Petugas     => 'Petugas',
            self::Koordinator => 'Koordinator Unit',
            self::AdminProdi  => 'Admin Prodi',
            self::SuperAdmin  => 'Super Admin',
            self::Ultksp      => 'Tim ULTKSP',
            self::Pimpinan    => 'Pimpinan Dekanat',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Mahasiswa   => 'b-netral',
            self::Petugas,
            self::Koordinator => 'b-verifikasi',
            self::AdminProdi,
            self::SuperAdmin  => 'b-admin',
            self::Ultksp      => 'b-proses',
            self::Pimpinan    => 'b-selesai',
        };
    }

    /** Halaman pertama setelah login. */
    public function beranda(): string
    {
        return match ($this) {
            self::Mahasiswa              => 'dashboard',
            self::Petugas,
            self::Koordinator            => 'petugas.antrian',
            self::AdminProdi,
            self::SuperAdmin,
            self::Ultksp,
            self::Pimpinan               => 'admin.rekap',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [
            self::AdminProdi, self::SuperAdmin, self::Ultksp, self::Pimpinan,
        ]);
    }

    public function isStaff(): bool
    {
        return $this !== self::Mahasiswa;
    }
}
