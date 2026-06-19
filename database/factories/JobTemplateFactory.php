<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\JobTemplateStatus;
use App\Models\JobTemplate;
use App\Models\Unit;
use App\Models\WorkflowTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobTemplate>
 */
class JobTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul_posisi' => $this->faker->jobTitle(),
            'unit_id' => Unit::factory(),
            'workflow_template_id' => WorkflowTemplate::factory(),
            'jenis_pekerjaan' => $this->faker->randomElement(EmploymentType::cases()),
            'deskripsi_pekerjaan' => $this->faker->paragraph(),
            'kualifikasi' => $this->faker->paragraph(),
            'status' => JobTemplateStatus::Active,
        ];
    }

    public function archived(): static
    {
        return $this->state(['status' => JobTemplateStatus::Archived]);
    }
}
