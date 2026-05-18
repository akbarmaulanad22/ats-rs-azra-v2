<?php

namespace Database\Factories;

use App\Models\WorkflowTemplateSnapshot;
use App\Models\WorkflowTemplateSnapshotStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowTemplateSnapshotStage>
 */
class WorkflowTemplateSnapshotStageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workflow_template_snapshot_id' => WorkflowTemplateSnapshot::factory(),
            'position' => fake()->numberBetween(1, 10),
            'key' => fake()->slug(2),
            'nama' => fake()->words(2, true),
            'is_locked_first' => false,
            'is_locked_last' => false,
        ];
    }
}
