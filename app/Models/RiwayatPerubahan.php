<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan audit satu perubahan (append-only).
 *
 * Tidak ada updated_at yang berarti: baris ditulis sekali lalu tidak diubah.
 */
class RiwayatPerubahan extends Model
{
    protected $table = 'riwayat_perubahan';

    protected $fillable = [
        'aspirasi_id', 'pengguna_id', 'aktor', 'aktor_peran',
        'entitas', 'entitas_id', 'aksi', 'field',
        'nilai_lama', 'nilai_baru', 'keterangan',
        'ip', 'perangkat', 'terjadi_pada',
    ];

    protected $casts = [
        'terjadi_pada' => 'datetime',
    ];

    public function aspirasi(): BelongsTo
    {
        return $this->belongsTo(Aspirasi::class, 'aspirasi_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    /** Label aksi untuk tampilan. */
    public function labelAksi(): string
    {
        return match ($this->aksi) {
            'dibuat'         => 'Data dibuat',
            'diubah'         => 'Data diubah',
            'dihapus'        => 'Data dihapus',
            'status'         => 'Perubahan status',
            'tindak_lanjut'  => 'Tindak lanjut',
            'eskalasi'       => 'Eskalasi',
            'akses_anonim'   => 'Pembukaan identitas anonim',
            default          => ucfirst(str_replace('_', ' ', $this->aksi)),
        };
    }

    /** Warna badge sesuai bobot peristiwa. */
    public function badge(): string
    {
        return match ($this->aksi) {
            'dihapus'       => 'b-tolak',
            'eskalasi'      => 'b-late',
            'akses_anonim'  => 'b-proses',
            'status'        => 'b-verifikasi',
            'tindak_lanjut' => 'b-selesai',
            default         => 'b-netral',
        };
    }
}
