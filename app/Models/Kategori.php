<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kategori extends Model
{
    protected $table = 'kategori';

    protected $fillable = [
        'nama', 'batas_hari', 'sla_respons_jam', 'sla_selesai_jam',
        'tingkat_keparahan', 'unit_default_id',
    ];

    public function unitDefault(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_default_id');
    }

    public function aspirasi(): HasMany
    {
        return $this->hasMany(Aspirasi::class, 'kategori_id');
    }

    /**
     * Apakah kategori masih dipakai aspirasi (cegah hapus via RESTRICT).
     */
    public function dipakai(): bool
    {
        return $this->aspirasi()->exists();
    }
}
