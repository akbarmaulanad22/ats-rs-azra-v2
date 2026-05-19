<?php

namespace Database\Factories;

use App\Models\InterviewTemplate;
use App\Models\Vacancy;
use App\Models\VacancyInterviewTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VacancyInterviewTemplate>
 */
class VacancyInterviewTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $stageKeys = ['wawancara_kepala_unit', 'wawancara_manajer_hr', 'wawancara_direktur'];

        return [
            'vacancy_id' => Vacancy::factory(),
            'interview_template_id' => InterviewTemplate::factory(),
            'stage_key' => fake()->randomElement($stageKeys),
        ];
    }
}
