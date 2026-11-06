<?php

namespace Database\Factories;

use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use App\Models\Kategori;
use App\Models\Pengguna;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aspirasi>
 */
class AspirasiFactory extends Factory
{
    protected $model = Aspirasi::class;

    public function definition(): array
    {
        $masuk = now()->subDays(2);
        $kategori = Kategori::factory();

        return [
            'pengguna_id' => Pengguna::factory()->mahasiswa(),
            'kategori_id' => $kategori,
            'unit_id' => Unit::factory(),
            'judul' => fake()->sentence(6),
            'isi' => fake()->paragraph(3),
            'status' => StatusAspirasi::Diajukan,
            'alasan_penolakan' => null,
            'tanggal_masuk' => $masuk,
            'tenggat' => $masuk->copy()->addDays(7),
            'selesai_pada' => null,
        ];
    }

    /** Aspirasi yang sudah melewati tenggat (untuk menguji RB-05). */
    public function terlambat(): static
    {
        return $this->state(fn () => [
            'tanggal_masuk' => now()->subDays(20),
            'tenggat' => now()->subDays(5),
        ]);
    }

    public function status(StatusAspirasi $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
