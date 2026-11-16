<?php

namespace App\Models;

use App\Enums\StatusAspirasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TindakLanjut extends Model
{
    protected $table = 'tindak_lanjut';

    protected $fillable = [
        'aspirasi_id', 'pengguna_id', 'status_awal', 'status_akhir',
        'tanggapan', 'bukti', 'tanggal',
    ];

    /*
     * status_awal & status_akhir adalah ENUM di basis data dan dipakai sebagai
     * objek StatusAspirasi di seluruh Blade (->value, ->badge()).
     * Tanpa cast ini keduanya terbaca sebagai string dan memicu
     * "Attempt to read property value on string".
     */
    protected $casts = [
        'tanggal'      => 'datetime',
        'status_awal'  => StatusAspirasi::class,
        'status_akhir' => StatusAspirasi::class,
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
