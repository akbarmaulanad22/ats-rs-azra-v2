<?php

namespace Database\Factories;

use App\Enums\InterviewTemplateType;
use App\Models\InterviewTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InterviewTemplate>
 */
class InterviewTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->words(3, true),
            'tipe' => fake()->randomElement(InterviewTemplateType::cases()),
        ];
    }

    public function kriteriaPenilaian(): static
    {
        return $this->state(['tipe' => InterviewTemplateType::KriteriaPenilaian]);
    }

    public function kesiapan(): static
    {
        return $this->state(['tipe' => InterviewTemplateType::Kesiapan]);
    }
}
