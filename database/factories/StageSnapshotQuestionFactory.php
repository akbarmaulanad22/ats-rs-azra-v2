<?php

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Models\StageSnapshotQuestion;
use App\Models\WorkflowTemplateSnapshotStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StageSnapshotQuestion>
 */
class StageSnapshotQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workflow_template_snapshot_stage_id' => WorkflowTemplateSnapshotStage::factory(),
            'urutan' => fake()->numberBetween(1, 10),
            'tipe' => QuestionType::Mc->value,
            'pertanyaan' => fake()->sentence().'?',
            'nilai_poin' => fake()->numberBetween(1, 10),
        ];
    }

    public function essay(): static
    {
        return $this->state(['tipe' => QuestionType::Essay->value]);
    }
}
