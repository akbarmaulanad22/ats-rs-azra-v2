<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\InterviewResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InterviewResult>
 */
class InterviewResultFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'application_stage_id' => ApplicationStage::factory(),
            'interviewer_id' => User::factory(),
            'keputusan' => fake()->randomElement(['lulus', 'gagal', 'reserved']),
            'catatan' => fake()->optional()->sentence(),
            'submitted_at' => now(),
        ];
    }
}
