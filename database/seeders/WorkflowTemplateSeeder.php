<?php

namespace Database\Seeders;

use App\Models\WorkflowStage;
use App\Models\WorkflowTemplate;
use Illuminate\Database\Seeder;

class WorkflowTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $allStageKeys = [
            'application', 'personal_data_form', 'hr_cv_screening', 'unit_head_cv_screening',
            'invitation_email', 'competency_test', 'unit_head_interview', 'hr_manager_interview',
            'director_interview', 'disc_test', 'mbti_test', 'offering_letter', 'mcu', 'onboarding',
        ];

        $staffKeys = array_values(array_filter($allStageKeys, fn ($k) => $k !== 'director_interview'));

        $headUnitKeys = array_values(array_filter(
            $allStageKeys,
            fn ($k) => ! in_array($k, ['unit_head_cv_screening', 'unit_head_interview'])
        ));

        $templates = [
            ['name' => 'Koordinator', 'description' => 'Template rekrutmen untuk posisi Koordinator (semua tahap).',        'keys' => $allStageKeys],
            ['name' => 'Staf',        'description' => 'Template rekrutmen untuk posisi Staf (tanpa wawancara direktur).', 'keys' => $staffKeys],
            ['name' => 'Kepala Unit', 'description' => 'Template rekrutmen untuk posisi Kepala Unit (tanpa seleksi dan wawancara kepala unit).', 'keys' => $headUnitKeys],
        ];

        foreach ($templates as $data) {
            $template = WorkflowTemplate::updateOrCreate(
                ['name' => $data['name']],
                ['description' => $data['description']]
            );

            $stages = WorkflowStage::whereIn('key', $data['keys'])->get()->keyBy('key');

            $sync = [];
            foreach ($data['keys'] as $position => $key) {
                if ($stage = $stages->get($key)) {
                    $sync[$stage->id] = ['position' => $position + 1];
                }
            }

            $template->stages()->sync($sync);
        }
    }
}
