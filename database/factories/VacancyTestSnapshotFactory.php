<?php

namespace Database\Factories;

use App\Models\VacancyTest;
use App\Models\VacancyTestSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VacancyTestSnapshot>
 */
class VacancyTestSnapshotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vacancy_test_id' => VacancyTest::factory(),
            'batas_waktu_menit' => fake()->numberBetween(30, 120),
        ];
    }
}
