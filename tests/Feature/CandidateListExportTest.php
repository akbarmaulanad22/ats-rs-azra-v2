<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Exports\CandidateListExport;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class CandidateListExportTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    private function createVacancy(array $stageKeys = ['aplikasi', 'skrining_cv_hr', 'onboarding']): Vacancy
    {
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->create([
            'unit_id' => Unit::factory()->create()->id,
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);
    }

    /** @param array<int, ApplicationStageStatus> $stageStatuses */
    private function makeApplication(Vacancy $vacancy, array $stageStatuses = [], ?string $candidateName = null): Application
    {
        $vacancy->load('workflowTemplateSnapshot.stages');

        $candidate = Candidate::factory()->create(
            $candidateName ? ['nama_lengkap' => $candidateName] : []
        );

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $candidate->id,
        ]);

        $snapshotStages = $vacancy->workflowTemplateSnapshot->stages->sortBy('position')->values();

        foreach ($snapshotStages as $index => $stage) {
            $status = $stageStatuses[$index] ?? ($index === 0
                ? ApplicationStageStatus::Selesai
                : ($index === 1 ? ApplicationStageStatus::Aktif : ApplicationStageStatus::Pending));

            ApplicationStage::factory()->create([
                'application_id' => $application->id,
                'position' => $stage->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $status,
            ]);
        }

        return $application;
    }

    private function hrAdmin(): User
    {
        return User::factory()->create(['role' => Role::HrAdmin]);
    }

    private function expectedFilename(Vacancy $vacancy, string $ext = 'xlsx'): string
    {
        $date = now()->format('d-m-Y');
        $slug = str($vacancy->judul_posisi)->slug();

        return "daftar-kandidat-{$slug}-{$date}.{$ext}";
    }

    public function test_hr_admin_can_download_xlsx(): void
    {
        $this->seedStages();
        Excel::fake();

        $vacancy = $this->createVacancy();
        $this->makeApplication($vacancy);

        $this->actingAs($this->hrAdmin())
            ->get(route('lowongan.export.list', ['lowongan' => $vacancy, 'format' => 'xlsx']))
            ->assertOk();

        Excel::assertDownloaded($this->expectedFilename($vacancy, 'xlsx'));
    }

    public function test_hr_admin_can_download_csv(): void
    {
        $this->seedStages();
        Excel::fake();

        $vacancy = $this->createVacancy();
        $this->makeApplication($vacancy);

        $this->actingAs($this->hrAdmin())
            ->get(route('lowongan.export.list', ['lowongan' => $vacancy, 'format' => 'csv']))
            ->assertOk();

        Excel::assertDownloaded($this->expectedFilename($vacancy, 'csv'));
    }

    public function test_export_contains_correct_columns(): void
    {
        $this->seedStages();
        Excel::fake();

        $vacancy = $this->createVacancy();
        $this->makeApplication($vacancy);

        $this->actingAs($this->hrAdmin())
            ->get(route('lowongan.export.list', ['lowongan' => $vacancy]));

        Excel::assertDownloaded($this->expectedFilename($vacancy, 'xlsx'), function (CandidateListExport $export) {
            $headings = $export->headings();

            return in_array('Nama Kandidat', $headings)
                && in_array('Email', $headings)
                && in_array('No. Telepon', $headings)
                && in_array('Tanggal Melamar', $headings)
                && in_array('Tahap Saat Ini', $headings)
                && in_array('Status', $headings)
                && in_array('Skor Tes Kompetensi', $headings);
        });
    }

    public function test_export_only_includes_applications_for_the_vacancy(): void
    {
        $this->seedStages();

        $vacancy1 = $this->createVacancy();
        $vacancy2 = $this->createVacancy();

        $app1 = $this->makeApplication($vacancy1);
        $this->makeApplication($vacancy2);

        $export = new CandidateListExport($vacancy1, []);
        $results = $export->query()->get();

        $this->assertCount(1, $results);
        $this->assertEquals($app1->id, $results->first()->id);
    }

    public function test_export_filters_by_stage(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $appInHrScreening = $this->makeApplication($vacancy, [
            ApplicationStageStatus::Selesai,
            ApplicationStageStatus::Aktif,
            ApplicationStageStatus::Pending,
        ]);

        $appInApplication = $this->makeApplication($vacancy, [
            ApplicationStageStatus::Aktif,
            ApplicationStageStatus::Pending,
            ApplicationStageStatus::Pending,
        ]);

        $export = new CandidateListExport($vacancy, ['stage' => 'skrining_cv_hr']);
        $results = $export->query()->get();

        $ids = $results->pluck('id')->toArray();
        $this->assertContains($appInHrScreening->id, $ids);
        $this->assertNotContains($appInApplication->id, $ids);
    }

    public function test_export_filters_by_search(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy();

        $targetApp = $this->makeApplication($vacancy, [], 'Budi Santoso');
        $otherApp = $this->makeApplication($vacancy, [], 'Siti Rahma');

        $export = new CandidateListExport($vacancy, ['search' => 'budi']);
        $results = $export->query()->get();

        $ids = $results->pluck('id')->toArray();
        $this->assertContains($targetApp->id, $ids);
        $this->assertNotContains($otherApp->id, $ids);
    }

    public function test_non_hr_admin_cannot_export(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy();

        foreach ([Role::HrManager, Role::UnitHead, Role::Director, Role::Employee] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('lowongan.export.list', $vacancy))
                ->assertForbidden();
        }
    }

    public function test_unauthenticated_user_cannot_export(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy();

        $this->get(route('lowongan.export.list', $vacancy))
            ->assertRedirect(route('login'));
    }
}
