<?php

namespace Database\Factories;

use App\Models\InterviewCriteria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InterviewCriteria>
 */
class InterviewCriteriaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $stageKeys = ['wawancara_kepala_unit', 'wawancara_manajer_hr', 'wawancara_direktur'];

        return [
            'stage_key' => fake()->randomElement($stageKeys),
            'nama' => fake()->words(3, true),
            'urutan' => fake()->numberBetween(1, 10),
        ];
    }
}
