<?php

namespace Database\Factories;

use App\Models\CallbackInvite;
use App\Models\Candidate;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CallbackInvite>
 */
class CallbackInviteFactory extends Factory
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
            'candidate_id' => Candidate::factory(),
            'invited_by' => User::factory(),
            'invited_at' => now(),
        ];
    }
}
