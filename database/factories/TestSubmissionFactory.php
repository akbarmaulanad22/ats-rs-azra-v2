<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\TestSubmission;
use App\Models\VacancyTest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TestSubmission>
 */
class TestSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'vacancy_test_id' => VacancyTest::factory(),
            'token' => Str::uuid()->toString(),
            'started_at' => null,
            'submitted_at' => null,
            'total_skor' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state([
            'started_at' => now()->subMinutes(30),
            'submitted_at' => now(),
            'total_skor' => fake()->numberBetween(0, 100),
        ]);
    }
}
