<?php

namespace Database\Seeders;

use App\Models\InterviewCriteria;
use Illuminate\Database\Seeder;

class InterviewCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            'wawancara_kepala_unit' => [
                'Pengetahuan Teknis',
                'Pengalaman Relevan',
                'Kemampuan Problem Solving',
                'Komunikasi',
                'Kerjasama Tim',
            ],
            'wawancara_manajer_hr' => [
                'Kesesuaian Budaya Organisasi',
                'Motivasi & Komitmen',
                'Integritas',
                'Kemampuan Kepemimpinan',
                'Kemampuan Adaptasi',
            ],
            'wawancara_direktur' => [
                'Visi & Kepemimpinan Strategis',
                'Pengambilan Keputusan',
                'Kemampuan Manajerial',
                'Integritas Profesional',
            ],
        ];

        foreach ($criteria as $stageKey => $namaList) {
            foreach ($namaList as $urutan => $nama) {
                InterviewCriteria::firstOrCreate(
                    ['stage_key' => $stageKey, 'nama' => $nama],
                    ['urutan' => $urutan + 1],
                );
            }
        }
    }
}
