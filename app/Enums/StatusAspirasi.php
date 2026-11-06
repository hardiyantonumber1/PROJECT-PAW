<?php

namespace App\Enums;

/**
 * Nilai ENUM aspirasi.status & tindak_lanjut.status_awal/status_akhir
 * (Rancangan_Struktur_Basis_Data.md §5, aturan RB-01).
 */
enum StatusAspirasi: string
{
    case Diajukan = 'Diajukan';
    case Diverifikasi = 'Diverifikasi';
    case Diproses = 'Diproses';
    case Selesai = 'Selesai';
    case Ditolak = 'Ditolak';

    public function badge(): string
    {
        return match ($this) {
            self::Diajukan => 'b-diajukan',
            self::Diverifikasi => 'b-verifikasi',
            self::Diproses => 'b-proses',
            self::Selesai => 'b-selesai',
            self::Ditolak => 'b-tolak',
        };
    }

    /** Warna garis donut / legenda. */
    public function warna(): string
    {
        return match ($this) {
            self::Diajukan => '#2563EB',
            self::Diverifikasi => '#6D28D9',
            self::Diproses => '#F59E0B',
            self::Selesai => '#0E9F6E',
            self::Ditolak => '#DC2626',
        };
    }

    public function keterangan(): string
    {
        return match ($this) {
            self::Diajukan => 'Aspirasi valid dan diteruskan ke unit pelaksana.',
            self::Diverifikasi => 'Aspirasi sudah diverifikasi petugas.',
            self::Diproses => 'Sedang ditangani unit terkait.',
            self::Selesai => 'Penanganan tuntas.',
            self::Ditolak => 'Aspirasi tidak dapat ditindaklanjuti.',
        };
    }

    /**
     * Transisi status yang diizinkan (RB-01: maju berurutan,
     * Selesai/Ditolak bersifat final).
     *
     * @return array<int, self>
     */
    public function berikutnya(): array
    {
        return match ($this) {
            self::Diajukan => [self::Diverifikasi, self::Ditolak],
            self::Diverifikasi => [self::Diproses, self::Ditolak],
            self::Diproses => [self::Selesai, self::Ditolak],
            self::Selesai, self::Ditolak => [],
        };
    }

    public function bolehBerubahKe(self $tujuan): bool
    {
        return in_array($tujuan, $this->berikutnya(), true);
    }

    public function final(): bool
    {
        return $this->berikutnya() === [];
    }

    /** Status dianggap masih berjalan (belum selesai/ditolak). */
    public function berjalan(): bool
    {
        return ! $this->final();
    }
}
