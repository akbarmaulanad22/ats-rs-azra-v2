<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Unit;
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
            'unit_id' => Unit::inRandomOrder()->value('id') ?? Unit::factory(),
            'posisi_pekerjaan' => fake()->jobTitle(),
            'profesi' => fake()->randomElement(['Perawat', 'Dokter', 'Apoteker', 'Radiografer', 'Staf Administrasi']),
            'jabatan' => fake()->randomElement(['Staf', 'Koordinator', 'Kepala Unit', 'Manajer']),
        ];
    }
}
