<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\MbtiSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MbtiSubmission>
 */
class MbtiSubmissionFactory extends Factory
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
            'started_at' => now()->subMinutes(15),
            'submitted_at' => now(),
        ]);
    }
}
