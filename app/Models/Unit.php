<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends Model
{
    protected $table = 'unit';

    protected $fillable = ['nama', 'koordinator_id'];

    public function koordinator(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'koordinator_id');
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(Pengguna::class, 'unit_id');
    }

    public function kategoris(): HasMany
    {
        return $this->hasMany(Kategori::class, 'unit_default_id');
    }

    public function aspirasi(): HasMany
    {
        return $this->hasMany(Aspirasi::class, 'unit_id');
    }

    /**
     * Semua pengguna (petugas/koordinator) di unit ini.
     */
    public function petugas(): HasMany
    {
        return $this->hasMany(Pengguna::class, 'unit_id')
            ->whereIn('role', ['petugas', 'koordinator']);
    }

    /**
     * Apakah unit ini masih dipakai (ada aspirasi atau petugas aktif).
     */
    public function dipakai(): bool
    {
        return $this->aspirasi()->exists() || $this->pengguna()->exists();
    }

    /**
     * Scope: query Pengguna::petugas() dari luar unit.
     */
    public static function scopePetugas($query)
    {
        return $query->whereIn('role', ['petugas', 'koordinator']);
    }
}
