<?php

namespace Database\Factories;

use App\Models\Vacancy;
use App\Models\VacancyInterviewCriteria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VacancyInterviewCriteria>
 */
class VacancyInterviewCriteriaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $stageKeys = ['wawancara_kepala_unit', 'wawancara_manajer_hr', 'wawancara_direktur'];

        return [
            'vacancy_id' => Vacancy::factory(),
            'stage_key' => fake()->randomElement($stageKeys),
            'nama' => fake()->words(3, true),
            'urutan' => fake()->numberBetween(1, 10),
        ];
    }
}
