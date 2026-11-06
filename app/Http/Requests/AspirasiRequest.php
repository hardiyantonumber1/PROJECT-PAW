<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AspirasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'min:10', 'max:150'],
            'kategori_id' => ['required', 'integer', 'exists:kategori,id'],
            'unit_id' => ['required', 'integer', 'exists:unit,id'],
            'isi' => ['required', 'string', 'min:20', 'max:2000'],
            'pernyataan' => [$this->isMethod('POST') ? 'accepted' : 'nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul aspirasi wajib diisi.',
            'judul.min' => 'Judul minimal 10 karakter agar mudah dikenali petugas.',
            'judul.max' => 'Judul maksimal 150 karakter.',
            'kategori_id.required' => 'Pilih kategori aspirasi.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak tersedia.',
            'unit_id.required' => 'Pilih unit tujuan.',
            'unit_id.exists' => 'Unit yang dipilih tidak tersedia.',
            'isi.required' => 'Uraian aspirasi wajib diisi.',
            'isi.min' => 'Uraian minimal 20 karakter — jelaskan waktu, lokasi, dan dampaknya.',
            'isi.max' => 'Uraian maksimal 2.000 karakter.',
            'pernyataan.accepted' => 'Centang pernyataan bahwa informasi yang Anda kirim benar.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul' => 'judul aspirasi',
            'kategori_id' => 'kategori',
            'unit_id' => 'unit tujuan',
            'isi' => 'uraian aspirasi',
        ];
    }
}
