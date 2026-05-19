<?php

namespace Database\Factories;

use App\Models\InterviewReadinessAnswer;
use App\Models\InterviewResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InterviewReadinessAnswer>
 */
class InterviewReadinessAnswerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'interview_result_id' => InterviewResult::factory(),
            'pertanyaan' => fake()->sentence(),
            'jawaban' => fake()->boolean(),
            'interview_template_id' => null,
        ];
    }
}
