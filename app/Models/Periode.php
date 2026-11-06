<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Periode extends Model
{
    protected $table = 'periode';

    protected $fillable = [
        'kode', 'nama', 'tanggal_mulai', 'tanggal_selesai',
        'status', 'ditutup_oleh', 'ditutup_pada',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'ditutup_pada'    => 'datetime',
    ];

    public function penutup(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'ditutup_oleh');
    }

    public function aspirasi(): HasMany
    {
        return $this->hasMany(Aspirasi::class, 'periode_id');
    }

    public static function aktif(): ?self
    {
        return static::where('status', 'aktif')
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->first();
    }
}
