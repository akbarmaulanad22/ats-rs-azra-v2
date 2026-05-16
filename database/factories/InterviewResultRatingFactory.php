<?php

namespace Database\Factories;

use App\Models\InterviewResult;
use App\Models\InterviewResultRating;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InterviewResultRating>
 */
class InterviewResultRatingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'interview_result_id' => InterviewResult::factory(),
            'nama_kriteria' => fake()->words(3, true),
            'nilai' => fake()->numberBetween(1, 5),
        ];
    }
}
