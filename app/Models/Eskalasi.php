<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris eskalasi = satu kenaikan tingkat yang sudah terjadi.
 * Kombinasi (aspirasi_id, level) unik di tingkat basis data.
 */
class Eskalasi extends Model
{
    protected $table = 'eskalasi';

    /** Level eskalasi beserta artinya. */
    public const LABEL = [
        1 => 'Belum ada respons',
        2 => 'Tenggat terlampaui',
        3 => 'Pembiaran berulang',
    ];

    protected $fillable = [
        'aspirasi_id', 'level', 'level_sebelum', 'alasan', 'rincian',
        'penerima_peran', 'keterlambatan_jam', 'terjadi_pada',
    ];

    protected $casts = [
        'level'          => 'integer',
        'level_sebelum'  => 'integer',
        'terjadi_pada'   => 'datetime',
    ];

    public function aspirasi(): BelongsTo
    {
        return $this->belongsTo(Aspirasi::class, 'aspirasi_id');
    }

    public function label(): string
    {
        return self::LABEL[$this->level] ?? 'Level '.$this->level;
    }

    public function badge(): string
    {
        return match ($this->level) {
            1 => 'b-proses',
            2 => 'b-late',
            3 => 'b-tolak',
            default => 'b-netral',
        };
    }

    public function scopeLevelTertinggi(Builder $query): Builder
    {
        return $query->orderByDesc('level');
    }
}
