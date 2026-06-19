<?php

namespace Database\Factories;

use App\Models\JobTemplate;
use App\Models\JobTemplateTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobTemplateTest>
 */
class JobTemplateTestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_template_id' => JobTemplate::factory(),
            'batas_waktu_menit' => fake()->numberBetween(30, 120),
        ];
    }
}
