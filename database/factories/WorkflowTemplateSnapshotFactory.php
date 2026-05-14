<?php

namespace Database\Factories;

use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowTemplateSnapshot>
 */
class WorkflowTemplateSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => $this->faker->words(2, true),
        ];
    }
}
