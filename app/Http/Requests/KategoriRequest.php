<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KategoriRequest extends FormRequest
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
        $kategori = $this->route('kategori');

        return [
            'nama' => [
                'required', 'string', 'max:100',
                Rule::unique('kategori', 'nama')->ignore($kategori?->id),
            ],
            'batas_hari' => ['required', 'integer', 'min:1', 'max:365'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.unique' => 'Nama kategori ini sudah dipakai (indeks UNIQUE ux_kategori_nama).',
            'batas_hari.required' => 'Batas hari penanganan wajib diisi.',
            'batas_hari.min' => 'Batas hari minimal 1 hari.',
            'batas_hari.max' => 'Batas hari maksimal 365 hari.',
        ];
    }
}
