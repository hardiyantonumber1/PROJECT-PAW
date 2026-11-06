<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
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
        $unit = $this->route('unit');

        return [
            'nama' => [
                'required', 'string', 'max:100',
                Rule::unique('unit', 'nama')->ignore($unit?->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama unit wajib diisi.',
            'nama.unique' => 'Nama unit ini sudah ada (indeks UNIQUE ux_unit_nama).',
        ];
    }
}
