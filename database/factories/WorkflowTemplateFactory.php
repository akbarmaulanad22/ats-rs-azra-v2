<?php

namespace Database\Factories;

use App\Models\WorkflowStage;
use App\Models\WorkflowTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowTemplate>
 */
class WorkflowTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function withDefaultStages(): static
    {
        return $this->afterCreating(function (WorkflowTemplate $template) {
            $stages = WorkflowStage::orderBy('default_order')->get();

            $sync = $stages->mapWithKeys(fn ($stage, $idx) => [$stage->id => ['position' => $idx + 1]])->all();

            $template->stages()->sync($sync);
        });
    }
}
