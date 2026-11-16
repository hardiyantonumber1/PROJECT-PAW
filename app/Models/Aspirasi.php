<?php

namespace App\Models;

use App\Enums\StatusAspirasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Aspirasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'aspirasi';

    protected $fillable = [
        'pengguna_id', 'kategori_id', 'unit_id', 'prodi_id', 'periode_id',
        'judul', 'isi', 'status', 'tanggal_masuk', 'tenggat',
        'selesai_pada', 'kode', 'alasan_penolakan', 'is_anonim', 'penanggung_jawab_id',
    ];

    /*
     * level_eskalasi, eskalasi_terakhir_pada, dan ditangani_pada sengaja TIDAK
     * masuk $fillable: hanya EskalasiService yang boleh mengubahnya. Dengan
     * begitu tidak ada jalur mass-assignment dari form untuk memalsukan level.
     */

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'tenggat'       => 'datetime',
        'selesai_pada'  => 'datetime',
        'status'        => StatusAspirasi::class,
        'is_anonim'     => 'boolean',
        'level_eskalasi' => 'integer',
        'eskalasi_terakhir_pada' => 'datetime',
        'ditangani_pada' => 'datetime',
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

    /** Riwayat eskalasi (satu baris per level, unik di DB). */
    public function eskalasi(): HasMany
    {
        return $this->hasMany(Eskalasi::class, 'aspirasi_id')->orderBy('level');
    }

    /** Jejak audit seluruh perubahan atas aspirasi ini. */
    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatPerubahan::class, 'aspirasi_id')
            ->orderByDesc('terjadi_pada')
            ->orderByDesc('id');
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

    /** "3 hari lagi", "besok", "hari ini", "lewat 2 hari" — dipakai tabel. */
    public function getLabelTenggatAttribute(): string
    {
        if (! $this->tenggat) {
            return 'tanpa tenggat';
        }

        $hari = $this->hari_tersisa;

        return match (true) {
            $hari > 1  => $hari.' hari lagi',
            $hari === 1 => 'besok',
            $hari === 0 => 'hari ini',
            default    => 'lewat '.abs($hari).' hari',
        };
    }

    /** Persentase waktu terpakai terhadap tenggat (0–100). */
    public function getProgresAttribute(): int
    {
        if (! $this->tanggal_masuk || ! $this->tenggat) {
            return 0;
        }

        $total = max(1, (int) $this->tanggal_masuk->diffInMinutes($this->tenggat));
        $terpakai = (int) $this->tanggal_masuk->diffInMinutes(now(), false);

        return (int) max(0, min(100, round($terpakai / $total * 100)));
    }

    /** Label level eskalasi untuk tampilan. */
    public function getLabelEskalasiAttribute(): string
    {
        return Eskalasi::LABEL[$this->level_eskalasi] ?? 'Normal';
    }

    // ───────────── Privasi / anonimitas ─────────────

    /**
     * Siapa yang boleh melihat identitas pelapor?
     *
     * Aspirasi anonim: hanya pelapor itu sendiri dan Super Admin (keperluan
     * audit — setiap pembukaan tercatat di log_keamanan). Petugas, pimpinan,
     * dan ULTKSP hanya melihat "Anonim".
     */
    public function identitasTerbukaUntuk(?Pengguna $user): bool
    {
        if (! $this->is_anonim) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->id === $this->pengguna_id || $user->isSuperAdmin();
    }

    public function namaPelapor(?Pengguna $user = null): string
    {
        $user ??= auth()->user();

        if (! $this->identitasTerbukaUntuk($user)) {
            return 'Anonim';
        }

        return $this->pengguna?->nama ?? 'Pengguna dihapus';
    }

    public function inisialPelapor(?Pengguna $user = null): string
    {
        $user ??= auth()->user();

        return $this->identitasTerbukaUntuk($user)
            ? ($this->pengguna?->inisial ?? 'AN')
            : 'AN';
    }

    /** NPM/NIP tersamar: ************1234 → ****1234. */
    public function npmPelapor(?Pengguna $user = null): ?string
    {
        $user ??= auth()->user();

        if (! $this->identitasTerbukaUntuk($user)) {
            return null;
        }

        $npm = $this->pengguna?->npm_nip;

        return $npm ? str_repeat('*', max(0, mb_strlen($npm) - 4)).mb_substr($npm, -4) : null;
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

    /** Kandidat pemindaian eskalasi: masih berjalan & belum level tertinggi. */
    public function scopePerluDipindai(Builder $query): Builder
    {
        return $query->berjalan()->where('level_eskalasi', '<', 3);
    }

    /**
     * Pencarian bebas pada kode tiket, judul, dan uraian.
     * Wildcard LIKE dari input pengguna di-escape agar tidak jadi pola liar.
     */
    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        $kata = trim((string) $kata);

        if ($kata === '') {
            return $query;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $kata).'%';

        return $query->where(function (Builder $w) use ($like) {
            $w->where('judul', 'like', $like)
                ->orWhere('isi', 'like', $like)
                ->orWhere('kode', 'like', $like);
        });
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
