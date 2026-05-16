<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\DiscSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DiscSubmission>
 */
class DiscSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'token' => Str::uuid()->toString(),
            'started_at' => null,
            'submitted_at' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state([
            'started_at' => now()->subMinutes(10),
            'submitted_at' => now(),
        ]);
    }
}
