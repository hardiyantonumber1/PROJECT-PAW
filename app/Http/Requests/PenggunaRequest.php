<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pengguna = $this->route('pengguna');
        $baru = $pengguna === null;

        return [
            'nama' => ['required', 'string', 'max:100'],
            'email' => [
                'required', 'string', 'email', 'max:100',
                Rule::unique('pengguna', 'email')->ignore($pengguna?->id),
            ],
            'role' => ['required', Rule::enum(Role::class)],
            'unit_id' => ['nullable', 'integer', 'exists:unit,id'],
            'password' => [
                $baru ? 'required' : 'nullable',
                'confirmed',
                Password::min(8),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar (indeks UNIQUE ux_pengguna_email).',
            'role.required' => 'Pilih peran pengguna.',
            'unit_id.exists' => 'Unit yang dipilih tidak tersedia.',
            'password.required' => 'Kata sandi wajib diisi untuk akun baru.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ];
    }
}
