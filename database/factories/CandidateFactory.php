<?php

namespace Database\Factories;

use App\Models\Candidate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_lengkap' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'no_telepon' => fake()->numerify('08##########'),
            'nama_ibu_kandung' => fake()->name('female'),
            'kontak_darurat_nama' => fake()->name(),
            'kontak_darurat_no_telp' => fake()->numerify('08##########'),
            'kontak_darurat_hubungan' => fake()->randomElement(['Ibu', 'Ayah', 'Suami', 'Istri', 'Saudara']),
        ];
    }
}
