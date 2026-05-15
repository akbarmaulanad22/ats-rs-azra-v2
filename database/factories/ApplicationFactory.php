<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'candidate_id' => Candidate::factory(),
            'vacancy_id' => Vacancy::factory()->published(),
            'token' => Str::uuid()->toString(),
            'cv_path' => 'cv/'.Str::random(40).'.pdf',
        ];
    }
}
