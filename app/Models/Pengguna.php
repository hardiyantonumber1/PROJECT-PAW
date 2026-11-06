<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengguna extends Authenticatable
{
    use Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'nama', 'email', 'password', 'role',
        'unit_id', 'prodi_id', 'npm_nip', 'is_anonim_ok',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_anonim_ok'      => 'boolean',
        'role'              => Role::class,
    ];

    // ───────────── Role helpers ─────────────
    public function isMahasiswa(): bool    { return $this->role === Role::Mahasiswa; }
    public function isPetugas(): bool      { return in_array($this->role, [Role::Petugas, Role::Koordinator]); }
    public function isKoordinator(): bool  { return $this->role === Role::Koordinator; }
    public function isAdminProdi(): bool   { return $this->role === Role::AdminProdi; }
    public function isSuperAdmin(): bool   { return $this->role === Role::SuperAdmin; }
    public function isUltksp(): bool       { return $this->role === Role::Ultksp; }
    public function isPimpinan(): bool     { return $this->role === Role::Pimpinan; }

    public function isAdmin(): bool
    {
        return in_array($this->role, [
            Role::AdminProdi, Role::SuperAdmin,
        ]);
    }

    public function isStaff(): bool
    {
        return ! $this->isMahasiswa();
    }

    // ───────────── Relasi ─────────────
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function aspirasi(): HasMany
    {
        return $this->hasMany(Aspirasi::class, 'pengguna_id');
    }

    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(TindakLanjut::class, 'pengguna_id');
    }

    /**
     * Scope untuk petugas/koordinator aktif.
     */
    public function scopePetugas($query)
    {
        return $query->whereIn('role', ['petugas', 'koordinator']);
    }

    // ─── Computed properties ──────────────────────────────────────────────

    /**
     * Dua huruf awal nama untuk avatar/inisial.
     */
    public function getInisialAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->nama));
        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }
        return mb_strtoupper(mb_substr($this->nama, 0, 2));
    }

    /**
     * Nama yang dipotong setelah kata kedua, maks 20 karakter.
     */
    public function getNamaPendekAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->nama));
        $pendek = implode(' ', array_slice($parts, 0, 2));
        return mb_strlen($pendek) > 20 ? mb_substr($pendek, 0, 20).'…' : $pendek;
    }
}
