<?php

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionBankTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_bank_template_id' => QuestionBankTemplate::factory(),
            'tipe' => QuestionType::Mc->value,
            'pertanyaan' => fake()->sentence().'?',
            'nilai_poin' => fake()->numberBetween(1, 10),
            'urutan' => fake()->numberBetween(1, 50),
        ];
    }

    public function essay(): static
    {
        return $this->state(['tipe' => QuestionType::Essay->value]);
    }
}
