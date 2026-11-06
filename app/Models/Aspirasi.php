<?php

namespace App\Models;

use App\Enums\StatusAspirasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aspirasi extends Model
{
    protected $table = 'aspirasi';

    protected $fillable = [
        'pengguna_id', 'kategori_id', 'unit_id', 'prodi_id', 'periode_id',
        'judul', 'isi', 'status', 'tanggal_masuk', 'tenggat',
        'selesai_pada', 'kode', 'alasan_penolakan', 'is_anonim', 'penanggung_jawab_id',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'tenggat'       => 'datetime',
        'selesai_pada'  => 'datetime',
        'status'        => StatusAspirasi::class,
        'is_anonim'     => 'boolean',
    ];

    // ───────────── Relasi ─────────────
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'penanggung_jawab_id');
    }

    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(TindakLanjut::class, 'aspirasi_id');
    }

    public function lampirans(): HasMany
    {
        return $this->hasMany(Lampiran::class, 'aspirasi_id');
    }

    // ───────────── Computed attributes ─────────────
    /**
     * Selisih hari tenggat dari sekarang (negatif = terlambat).
     */
    public function getHariTersisaAttribute(): int
    {
        if (! $this->tenggat) {
            return 0;
        }

        return (int) now()->startOfDay()->diffInDays($this->tenggat->startOfDay(), false);
    }

    public function getTerlambatAttribute(): bool
    {
        return $this->tenggat
            && $this->tenggat->isPast()
            && $this->status->berjalan();
    }

    // ───────────── Query scopes ─────────────
    public function scopeBerjalan(Builder $query): Builder
    {
        return $query->whereIn('status', collect(StatusAspirasi::cases())
            ->filter(fn (StatusAspirasi $s) => $s->berjalan())
            ->map(fn (StatusAspirasi $s) => $s->value)
            ->all());
    }

    public function scopeTerlambat(Builder $query): Builder
    {
        return $query->berjalan()->where('tenggat', '<', now());
    }

    /**
     * Mahasiswa: hanya aspirasi miliknya.
     * Petugas/Koordinator: hanya aspirasi unit mereka.
     * Admin/Super Admin/Pimpinan/ULTKSP: semua aspirasi.
     */
    public function scopeTerlihatOleh(Builder $query, Pengguna $user): Builder
    {
        if ($user->isSuperAdmin() || $user->isPimpinan() || $user->isUltksp()) {
            return $query;
        }

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isKoordinator() || ($user->role->value === 'petugas')) {
            return $query->where('unit_id', $user->unit_id);
        }

        // mahasiswa
        return $query->where('pengguna_id', $user->id);
    }

    protected static function booted(): void
    {
        static::creating(function (Aspirasi $aspirasi) {
            if (empty($aspirasi->kode)) {
                $aspirasi->kode = static::generateKode();
            }
            if (empty($aspirasi->kode_tiket)) {
                $aspirasi->kode_tiket = $aspirasi->kode;
            }
        });
    }

    public static function generateKode(): string
    {
        $prefix = 'ASP-' . now()->format('Ymd') . '-';
        $last = static::where('kode', 'like', $prefix . '%')
            ->orderByDesc('kode')
            ->value('kode');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
