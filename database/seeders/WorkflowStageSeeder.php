<?php

namespace Database\Seeders;

use App\Models\WorkflowStage;
use Illuminate\Database\Seeder;

class WorkflowStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['key' => 'application',             'label' => 'Lamaran',                      'is_locked_first' => true,  'is_locked_last' => false, 'default_order' => 1],
            ['key' => 'personal_data_form',       'label' => 'Formulir Data Diri',            'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 2],
            ['key' => 'hr_cv_screening',          'label' => 'Seleksi CV oleh Admin HR',      'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 3],
            ['key' => 'unit_head_cv_screening',   'label' => 'Seleksi CV oleh Kepala Unit',   'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 4],
            ['key' => 'invitation_email',         'label' => 'Email Undangan',               'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 5],
            ['key' => 'competency_test',          'label' => 'Tes Kompetensi',               'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 6],
            ['key' => 'unit_head_interview',      'label' => 'Wawancara Kepala Unit',        'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 7],
            ['key' => 'hr_manager_interview',     'label' => 'Wawancara Manajer HR',         'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 8],
            ['key' => 'director_interview',       'label' => 'Wawancara Direktur',           'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 9],
            ['key' => 'disc_test',               'label' => 'Tes DiSC',                     'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 10],
            ['key' => 'mbti_test',               'label' => 'Tes MBTI',                     'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 11],
            ['key' => 'offering_letter',         'label' => 'Surat Penawaran',              'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 12],
            ['key' => 'mcu',                     'label' => 'MCU',                          'is_locked_first' => false, 'is_locked_last' => false, 'default_order' => 13],
            ['key' => 'onboarding',              'label' => 'Onboarding',                   'is_locked_first' => false, 'is_locked_last' => true,  'default_order' => 14],
        ];

        foreach ($stages as $stage) {
            WorkflowStage::updateOrCreate(['key' => $stage['key']], $stage);
        }
    }
}
