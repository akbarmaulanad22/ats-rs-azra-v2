<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'nip' => fake()->unique()->numerify('########'),
            'nama_karyawan' => fake()->name(),
            'unit' => fake()->randomElement(['ICU', 'IGD', 'HR', 'Finance', 'Farmasi', 'Radiologi']),
            'posisi_pekerjaan' => fake()->jobTitle(),
            'profesi' => fake()->randomElement(['Perawat', 'Dokter', 'Apoteker', 'Radiografer', 'Staf Administrasi']),
            'jabatan' => fake()->randomElement(['Staf', 'Koordinator', 'Kepala Unit', 'Manajer']),
        ];
    }
}
