<?php

namespace Database\Seeders;

use App\Enums\InterviewTemplateType;
use App\Models\InterviewTemplate;
use Illuminate\Database\Seeder;

class InterviewTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'nama' => 'Kriteria Umum - Kepala Unit',
                'tipe' => InterviewTemplateType::KriteriaPenilaian,
                'items' => [
                    'Pengetahuan Teknis',
                    'Pengalaman Relevan',
                    'Kemampuan Problem Solving',
                    'Komunikasi',
                    'Kerjasama Tim',
                ],
            ],
            [
                'nama' => 'Kriteria Umum - Manajer HR',
                'tipe' => InterviewTemplateType::KriteriaPenilaian,
                'items' => [
                    'Kesesuaian Budaya Organisasi',
                    'Motivasi & Komitmen',
                    'Integritas',
                    'Kemampuan Kepemimpinan',
                    'Kemampuan Adaptasi',
                ],
            ],
            [
                'nama' => 'Kriteria Umum - Direktur',
                'tipe' => InterviewTemplateType::KriteriaPenilaian,
                'items' => [
                    'Visi & Kepemimpinan Strategis',
                    'Pengambilan Keputusan',
                    'Kemampuan Manajerial',
                    'Integritas Profesional',
                ],
            ],
            [
                'nama' => 'Kesiapan Umum',
                'tipe' => InterviewTemplateType::Kesiapan,
                'items' => [
                    'Bersedia bekerja shift (pagi, siang, malam)?',
                    'Bersedia ditempatkan di unit mana saja sesuai kebutuhan?',
                    'Bersedia tidak menikah selama 1 tahun pertama?',
                    'Bersedia mengikuti masa percobaan selama 3 bulan?',
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            $template = InterviewTemplate::firstOrCreate(
                ['nama' => $templateData['nama']],
                ['tipe' => $templateData['tipe']],
            );

            if ($template->wasRecentlyCreated) {
                foreach ($templateData['items'] as $urutan => $teks) {
                    $template->items()->create([
                        'teks' => $teks,
                        'urutan' => $urutan + 1,
                    ]);
                }
            }
        }
    }
}
