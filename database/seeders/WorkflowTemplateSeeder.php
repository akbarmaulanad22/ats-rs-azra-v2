<?php

namespace Database\Seeders;

use App\Models\Stage;
use App\Models\WorkflowTemplate;
use Illuminate\Database\Seeder;

class WorkflowTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $allKeys = [
            'aplikasi', 'formulir_data_pribadi', 'skrining_cv_hr', 'skrining_cv_kepala_unit',
            'email_undangan', 'tes_kompetensi', 'wawancara_kepala_unit', 'wawancara_manajer_hr',
            'wawancara_direktur', 'tes_disc', 'tes_mbti', 'surat_penawaran', 'mcu', 'onboarding',
        ];

        $staffKeys = array_values(array_filter($allKeys, fn ($k) => $k !== 'wawancara_direktur'));

        $headUnitKeys = array_values(array_filter(
            $allKeys,
            fn ($k) => ! in_array($k, ['skrining_cv_kepala_unit', 'wawancara_kepala_unit']),
        ));

        $templates = [
            ['nama' => 'Koordinator', 'keys' => $allKeys],
            ['nama' => 'Staf', 'keys' => $staffKeys],
            ['nama' => 'Kepala Unit', 'keys' => $headUnitKeys],
        ];

        $stages = Stage::whereIn('key', $allKeys)->get()->keyBy('key');

        foreach ($templates as $templateData) {
            $template = WorkflowTemplate::firstOrCreate(['nama' => $templateData['nama']]);

            $pivot = [];
            foreach ($templateData['keys'] as $position => $key) {
                if (isset($stages[$key])) {
                    $pivot[$stages[$key]->id] = ['position' => $position + 1];
                }
            }

            $template->stages()->sync($pivot);
        }
    }
}
