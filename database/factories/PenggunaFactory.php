<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Pengguna;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pengguna>
 */
class PenggunaFactory extends Factory
{
    protected $model = Pengguna::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => Role::Mahasiswa,
            'unit_id' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function mahasiswa(): static
    {
        return $this->state(fn () => ['role' => Role::Mahasiswa, 'unit_id' => null]);
    }

    public function petugas(?Unit $unit = null): static
    {
        return $this->state(fn () => [
            'role' => Role::Petugas,
            'unit_id' => $unit?->id ?? Unit::factory(),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => Role::Admin, 'unit_id' => null]);
    }
}
