<?php

namespace App\Http\Requests;

use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TindakLanjutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->isMahasiswa();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Aspirasi|null $aspirasi */
        $aspirasi = $this->route('aspirasi');
        $boleh = $aspirasi
            ? array_map(fn (StatusAspirasi $s) => $s->value, $aspirasi->status->berikutnya())
            : [];

        // FR-16: status saat ini tetap boleh dipilih untuk menambah tanggapan saja.
        $tetap = $aspirasi && ! $aspirasi->status->final();
        if ($tetap) {
            $boleh[] = $aspirasi->status->value;
        }

        return [
            'status_akhir' => ['required', Rule::in($boleh)],
            'alasan_penolakan' => [
                Rule::requiredIf($this->input('status_akhir') === StatusAspirasi::Ditolak->value),
                'nullable',
                'string',
                'max:500',
            ],
            'tanggapan' => [
                Rule::requiredIf($tetap && $this->input('status_akhir') === $aspirasi->status->value),
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status_akhir.required' => 'Pilih status baru untuk aspirasi ini.',
            'status_akhir.in' => 'Perubahan status harus mengikuti alur Diajukan → Diverifikasi → Diproses → Selesai (RB-01).',
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi agar pelapor tahu penyebabnya.',
            'alasan_penolakan.max' => 'Alasan penolakan maksimal 500 karakter.',
            'tanggapan.max' => 'Tanggapan maksimal 1.000 karakter.',
            'tanggapan.required' => 'Tuliskan tanggapan bila status tidak diubah.',
        ];
    }
}
