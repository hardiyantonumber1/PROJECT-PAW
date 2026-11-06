<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lampiran extends Model
{
    protected $table = 'lampiran';

    protected $fillable = [
        'aspirasi_id', 'nama_file', 'path', 'mime', 'ukuran',
    ];

    public function aspirasi(): BelongsTo
    {
        return $this->belongsTo(Aspirasi::class, 'aspirasi_id');
    }
}
