<?php

namespace Database\Seeders;

use App\Enums\ApplicationStageStatus;
use App\Enums\EmploymentType;
use App\Enums\Role;
use App\Enums\VacancyStatus;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyCandidateSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'kepala_unit'],
            [
                'name' => 'Kepala Unit Demo',
                'username' => 'kepala_unit',
                'password' => Hash::make('password'),
                'role' => Role::UnitHead,
                'must_change_password' => false,
                'is_active' => true,
            ]
        );

        $template = WorkflowTemplate::where('nama', 'Koordinator')->firstOrFail();
        $template->load('stages');

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $unit = Unit::first();
        $vacancy = Vacancy::create([
            'judul_posisi' => 'Koordinator Medis (Demo)',
            'unit_id' => $unit->id,
            'workflow_template_snapshot_id' => $snapshot->id,
            'jenis_pekerjaan' => EmploymentType::FullTime,
            'deskripsi_pekerjaan' => 'Posisi demo untuk pengujian pipeline kandidat.',
            'kualifikasi' => 'Digunakan untuk data dummy.',
            'jumlah_posisi' => 12,
            'tenggat_lamaran' => now()->addMonths(3)->format('Y-m-d'),
            'status' => VacancyStatus::Published,
        ]);

        $snapshotStages = $snapshot->stages()->orderBy('position')->get();
        $lastIndex = $snapshotStages->count() - 1;

        foreach ($snapshotStages as $currentIndex => $targetStage) {
            $candidate = Candidate::create([
                'nama_lengkap' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'no_telepon' => fake()->numerify('08##########'),
            ]);

            $application = Application::create([
                'candidate_id' => $candidate->id,
                'vacancy_id' => $vacancy->id,
                'token' => Str::uuid()->toString(),
                'cv_path' => 'cv/'.Str::random(40).'.pdf',
                'alasan_melamar' => fake()->sentence(),
            ]);

            $stagesData = $snapshotStages->map(function ($stage, $index) use ($application, $currentIndex, $lastIndex): array {
                if ($currentIndex === $lastIndex) {
                    $status = ApplicationStageStatus::Selesai->value;
                } elseif ($index < $currentIndex) {
                    $status = ApplicationStageStatus::Selesai->value;
                } elseif ($index === $currentIndex) {
                    $status = ApplicationStageStatus::Aktif->value;
                } else {
                    $status = ApplicationStageStatus::Pending->value;
                }

                return [
                    'application_id' => $application->id,
                    'position' => $stage->position,
                    'key' => $stage->key,
                    'nama' => $stage->nama,
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            $application->stages()->insert($stagesData);
        }
    }
}
