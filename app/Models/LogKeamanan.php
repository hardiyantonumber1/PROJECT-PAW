<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Catatan peristiwa keamanan (masuk, gagal masuk, keluar, akses ditolak).
 *
 * Penulisan tidak boleh pernah menggagalkan permintaan pengguna: kegagalan
 * menulis log ditelan dan hanya dikirim ke berkas log aplikasi.
 */
class LogKeamanan extends Model
{
    protected $table = 'log_keamanan';

    public const AKSI = [
        'login_berhasil' => 'Masuk berhasil',
        'login_gagal'    => 'Percobaan masuk gagal',
        'login_diblokir' => 'Masuk diblokir (rate limit)',
        'logout'         => 'Keluar',
        'akses_ditolak'  => 'Akses ditolak',
        'akses_anonim'   => 'Identitas anonim dibuka',
    ];

    protected $fillable = [
        'pengguna_id', 'email', 'aksi', 'ip', 'perangkat', 'keterangan', 'terjadi_pada',
    ];

    protected $casts = [
        'terjadi_pada' => 'datetime',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function label(): string
    {
        return self::AKSI[$this->aksi] ?? $this->aksi;
    }

    public function badge(): string
    {
        return match ($this->aksi) {
            'login_gagal', 'login_diblokir', 'akses_ditolak' => 'b-tolak',
            'akses_anonim' => 'b-proses',
            'login_berhasil' => 'b-selesai',
            default => 'b-netral',
        };
    }

    /**
     * Tulis satu peristiwa keamanan. Tidak pernah melempar exception.
     */
    public static function catat(
        string $aksi,
        ?Pengguna $pengguna = null,
        ?string $email = null,
        ?string $keterangan = null,
    ): void {
        try {
            $request = app()->runningInConsole() ? null : request();

            static::create([
                'pengguna_id'  => $pengguna?->id,
                'email'        => $email ? self::samarkanEmail($email) : null,
                'aksi'         => $aksi,
                'ip'           => $request?->ip(),
                'perangkat'    => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 250) : null,
                'keterangan'   => $keterangan,
                'terjadi_pada' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Gagal menulis log keamanan: '.$e->getMessage());
        }
    }

    /** ma***@student.ub.ac.id — cukup untuk korelasi, tidak cukup untuk mengenali. */
    public static function samarkanEmail(?string $email): ?string
    {
        if (! $email || ! str_contains($email, '@')) {
            return $email;
        }

        [$nama, $domain] = explode('@', $email, 2);
        $awal = mb_substr($nama, 0, 2);

        return $awal.'***@'.$domain;
    }
}
