<?php

namespace Database\Factories;

use App\Enums\ApplicationStageStatus;
use App\Models\Application;
use App\Models\ApplicationStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationStage>
 */
class ApplicationStageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'position' => 1,
            'key' => 'lamaran',
            'nama' => 'Lamaran',
            'status' => ApplicationStageStatus::Selesai,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => ApplicationStageStatus::Pending]);
    }

    public function aktif(): static
    {
        return $this->state(['status' => ApplicationStageStatus::Aktif]);
    }

    public function reserved(): static
    {
        return $this->state(['status' => ApplicationStageStatus::Reserved]);
    }
}
