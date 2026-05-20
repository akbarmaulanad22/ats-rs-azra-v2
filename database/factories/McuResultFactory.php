<?php

namespace Database\Factories;

use App\Enums\McuStatus;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\McuResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<McuResult>
 */
class McuResultFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'application_stage_id' => ApplicationStage::factory(),
            'reviewer_id' => User::factory(),
            'keputusan' => McuStatus::Lulus,
            'dokumen_path' => null,
            'catatan' => null,
            'submitted_at' => now(),
        ];
    }
}
