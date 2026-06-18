<?php

namespace Database\Seeders;

use App\Enums\EmploymentType;
use App\Models\Unit;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Database\Seeder;

class VacancySeeder extends Seeder
{
    public function run(): void
    {
        $units = Unit::all();
        $template = WorkflowTemplate::with('stages')->first();

        if ($units->isEmpty() || ! $template) {
            return;
        }

        $positions = [
            ['Perawat ICU', EmploymentType::FullTime],
            ['Dokter Umum IGD', EmploymentType::FullTime],
            ['Apoteker Klinis', EmploymentType::FullTime],
            ['Radiografer', EmploymentType::Contract],
            ['Analis Laboratorium', EmploymentType::FullTime],
            ['Bidan Pelaksana', EmploymentType::Contract],
        ];

        foreach ($positions as [$judul, $jenis]) {
            $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

            Vacancy::factory()
                ->published()
                ->withGeneratedFlyer()
                ->create([
                    'judul_posisi' => $judul,
                    'jenis_pekerjaan' => $jenis,
                    'unit_id' => $units->random()->id,
                    'workflow_template_snapshot_id' => $snapshot->id,
                ]);
        }
    }
}
