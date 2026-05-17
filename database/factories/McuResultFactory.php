<?php

namespace Database\Factories;

use App\Enums\McuStatus;
use App\Models\Application;
use App\Models\McuResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<McuResult>
 */
class McuResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'status' => McuStatus::Dijadwalkan,
            'dokumen_path' => null,
            'catatan' => null,
        ];
    }
}
