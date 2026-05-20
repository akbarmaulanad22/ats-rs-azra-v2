<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['key' => 'lamaran', 'nama' => 'Lamaran', 'is_locked_first' => true, 'is_locked_last' => false],
            ['key' => 'skrining_cv_hr', 'nama' => 'Skrining CV HR', 'is_locked_first' => false, 'is_locked_last' => false],
            ['key' => 'skrining_cv_user', 'nama' => 'Skrining CV User', 'is_locked_first' => false, 'is_locked_last' => false],
            ['key' => 'tes_kompetensi', 'nama' => 'Tes Kompetensi', 'is_locked_first' => false, 'is_locked_last' => false],
            ['key' => 'wawancara_kepala_unit', 'nama' => 'Wawancara Kepala Unit', 'is_locked_first' => false, 'is_locked_last' => false],
            ['key' => 'wawancara_manajer_hr', 'nama' => 'Wawancara Manajer HR', 'is_locked_first' => false, 'is_locked_last' => false],
            ['key' => 'wawancara_direktur', 'nama' => 'Wawancara Direktur', 'is_locked_first' => false, 'is_locked_last' => false],
            ['key' => 'tes_disc', 'nama' => 'Tes DiSC', 'is_locked_first' => false, 'is_locked_last' => false],
            ['key' => 'tes_mbti', 'nama' => 'Tes MBTI', 'is_locked_first' => false, 'is_locked_last' => false],
            ['key' => 'surat_penawaran', 'nama' => 'Surat Penawaran', 'is_locked_first' => false, 'is_locked_last' => false],
            ['key' => 'mcu', 'nama' => 'MCU', 'is_locked_first' => false, 'is_locked_last' => false],
            ['key' => 'onboarding', 'nama' => 'Onboarding', 'is_locked_first' => false, 'is_locked_last' => true],
        ];

        foreach ($stages as $stage) {
            Stage::firstOrCreate(['key' => $stage['key']], $stage);
        }
    }
}
