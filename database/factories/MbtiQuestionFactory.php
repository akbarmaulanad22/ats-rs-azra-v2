<?php

namespace Database\Factories;

use App\Enums\MbtiPole;
use App\Models\MbtiQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MbtiQuestion>
 */
class MbtiQuestionFactory extends Factory
{
    public function definition(): array
    {
        $dichotomies = [
            ['dikotomi' => 'EI', 'kutub_a' => MbtiPole::E],
            ['dikotomi' => 'SN', 'kutub_a' => MbtiPole::S],
            ['dikotomi' => 'TF', 'kutub_a' => MbtiPole::T],
            ['dikotomi' => 'JP', 'kutub_a' => MbtiPole::J],
        ];
        $pair = fake()->randomElement($dichotomies);

        return [
            'urutan' => fake()->unique()->numberBetween(1, 999),
            'dikotomi' => $pair['dikotomi'],
            'pernyataan_a' => fake()->sentence(),
            'kutub_a' => $pair['kutub_a'],
            'pernyataan_b' => fake()->sentence(),
        ];
    }
}
