<?php

namespace Database\Factories;

use App\Models\StageSnapshotOption;
use App\Models\StageSnapshotQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StageSnapshotOption>
 */
class StageSnapshotOptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'stage_snapshot_question_id' => StageSnapshotQuestion::factory(),
            'teks_opsi' => fake()->sentence(),
            'is_correct' => false,
        ];
    }

    public function correct(): static
    {
        return $this->state(['is_correct' => true]);
    }
}
