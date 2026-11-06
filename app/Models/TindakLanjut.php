<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TindakLanjut extends Model
{
    protected $table = 'tindak_lanjut';

    protected $fillable = [
        'aspirasi_id', 'pengguna_id', 'status_awal', 'status_akhir',
        'tanggapan', 'bukti', 'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function aspirasi(): BelongsTo
    {
        return $this->belongsTo(Aspirasi::class, 'aspirasi_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }
}
