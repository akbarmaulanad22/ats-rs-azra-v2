<?php

namespace Database\Factories;

use App\Models\InterviewTemplate;
use App\Models\InterviewTemplateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InterviewTemplateItem>
 */
class InterviewTemplateItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'interview_template_id' => InterviewTemplate::factory(),
            'teks' => fake()->sentence(3),
            'urutan' => fake()->numberBetween(1, 10),
        ];
    }
}
