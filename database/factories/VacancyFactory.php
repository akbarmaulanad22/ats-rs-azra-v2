<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Models\Unit;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vacancy>
 */
class VacancyFactory extends Factory
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
            'jumlah_posisi' => $this->faker->numberBetween(1, 10),
            'tenggat_lamaran' => $this->faker->dateTimeBetween('+1 week', '+3 months')->format('Y-m-d'),
            'status' => VacancyStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => VacancyStatus::Published]);
    }

    public function closed(): static
    {
        return $this->state(['status' => VacancyStatus::Closed]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => VacancyStatus::Published,
            'tenggat_lamaran' => now()->subDay()->format('Y-m-d'),
        ]);
    }
}
