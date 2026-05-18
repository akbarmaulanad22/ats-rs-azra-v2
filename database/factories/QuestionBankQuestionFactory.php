<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionBankQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionBankQuestion>
 */
class QuestionBankQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_bank_id' => QuestionBank::factory(),
            'question_id' => Question::factory(),
            'urutan' => fake()->numberBetween(1, 100),
        ];
    }
}
