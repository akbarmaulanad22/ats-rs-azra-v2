<?php

namespace Database\Factories;

use App\Models\MbtiAnswer;
use App\Models\MbtiQuestion;
use App\Models\MbtiSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MbtiAnswer>
 */
class MbtiAnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mbti_submission_id' => MbtiSubmission::factory(),
            'mbti_question_id' => MbtiQuestion::factory(),
            'pilihan' => fake()->randomElement(['A', 'B']),
        ];
    }
}
