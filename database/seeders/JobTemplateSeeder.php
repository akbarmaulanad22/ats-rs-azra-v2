<?php

namespace Database\Seeders;

use App\Enums\EmploymentType;
use App\Enums\JobTemplateStatus;
use App\Models\InterviewTemplate;
use App\Models\JobTemplate;
use App\Models\JobTemplateInterviewTemplate;
use App\Models\Unit;
use App\Models\WorkflowTemplate;
use Illuminate\Database\Seeder;

class JobTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $units = Unit::all();
        $workflow = WorkflowTemplate::with('stages')->firstWhere('nama', 'Staf')
            ?? WorkflowTemplate::with('stages')->first();

        if ($units->isEmpty() || ! $workflow) {
            return;
        }

        $definitions = [
            ['Perawat Pelaksana', EmploymentType::FullTime, JobTemplateStatus::Active],
            ['Dokter Umum', EmploymentType::FullTime, JobTemplateStatus::Active],
            ['Apoteker', EmploymentType::Contract, JobTemplateStatus::Active],
            ['Petugas Rekam Medis', EmploymentType::PartTime, JobTemplateStatus::Archived],
        ];

        $wawancaraStages = $workflow->stages
            ->filter(fn ($s) => str_starts_with($s->key, 'wawancara_'));

        $interviewTemplates = InterviewTemplate::all();

        foreach ($definitions as [$judul, $jenis, $status]) {
            $template = JobTemplate::firstOrCreate(
                ['judul_posisi' => $judul],
                [
                    'unit_id' => $units->random()->id,
                    'workflow_template_id' => $workflow->id,
                    'jenis_pekerjaan' => $jenis,
                    'deskripsi_pekerjaan' => "Bertanggung jawab atas tugas {$judul} di lingkungan RS Azra sesuai standar pelayanan.",
                    'kualifikasi' => "Pendidikan relevan, memiliki STR aktif (bila berlaku), serta pengalaman di bidang {$judul}.",
                    'status' => $status,
                ],
            );

            if ($interviewTemplates->isEmpty() || $wawancaraStages->isEmpty()) {
                continue;
            }

            foreach ($wawancaraStages as $stage) {
                JobTemplateInterviewTemplate::firstOrCreate([
                    'job_template_id' => $template->id,
                    'interview_template_id' => $interviewTemplates->random()->id,
                    'stage_key' => $stage->key,
                ]);
            }
        }
    }
}
