<?php

namespace Database\Factories;

use App\Models\Vacancy;
use App\Models\VacancyTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VacancyTest>
 */
class VacancyTestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vacancy_id' => Vacancy::factory(),
            'batas_waktu_menit' => fake()->numberBetween(30, 120),
        ];
    }
}
