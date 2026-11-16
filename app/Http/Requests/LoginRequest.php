<?php

namespace App\Http\Requests;

use App\Models\LogKeamanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Validasi + autentikasi + pembatasan laju percobaan masuk.
 *
 * Keamanan yang diterapkan:
 *  - Maksimal 5 percobaan per menit per kombinasi email + IP (anti brute force).
 *  - Pesan galat selalu sama ("Email atau kata sandi tidak cocok") sehingga
 *    penyerang tidak bisa menebak email mana yang terdaftar (anti enumerasi).
 *  - Setiap percobaan dicatat di log_keamanan dengan email tersamar.
 */
class LoginRequest extends FormRequest
{
    public const MAKS_PERCOBAAN = 5;
    public const JEDA_DETIK = 60;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    /**
     * Jalankan autentikasi atau lemparkan ValidationException.
     */
    public function authenticate(): void
    {
        $this->pastikanBelumDibatasi();

        $berhasil = Auth::attempt(
            $this->only('email', 'password'),
            $this->boolean('remember')
        );

        if (! $berhasil) {
            RateLimiter::hit($this->kunciPembatasan(), self::JEDA_DETIK);

            LogKeamanan::catat(
                'login_gagal',
                null,
                (string) $this->input('email'),
                'Kredensial tidak cocok.'
            );

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak cocok.',
            ]);
        }

        RateLimiter::clear($this->kunciPembatasan());

        LogKeamanan::catat('login_berhasil', Auth::user(), null, 'Masuk melalui formulir.');
    }

    /** Tolak lebih awal bila kuota percobaan habis. */
    private function pastikanBelumDibatasi(): void
    {
        $kunci = $this->kunciPembatasan();

        if (! RateLimiter::tooManyAttempts($kunci, self::MAKS_PERCOBAAN)) {
            return;
        }

        $detik = RateLimiter::availableIn($kunci);

        LogKeamanan::catat(
            'login_diblokir',
            null,
            (string) $this->input('email'),
            'Rate limit tercapai; sisa jeda '.$detik.' detik.'
        );

        throw ValidationException::withMessages([
            'email' => 'Terlalu banyak percobaan masuk. Coba lagi dalam '.$detik.' detik.',
        ]);
    }

    private function kunciPembatasan(): string
    {
        return Str::transliterate(
            Str::lower((string) $this->input('email')).'|'.$this->ip()
        );
    }
}
