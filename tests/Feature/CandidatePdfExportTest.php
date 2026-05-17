<?php

namespace Tests\Feature;

use App\Actions\BuildCandidateProfileData;
use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\DiscResult;
use App\Models\DiscSubmission;
use App\Models\MbtiResult;
use App\Models\MbtiSubmission;
use App\Models\McuResult;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidatePdfExportTest extends TestCase
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
    private function makeApplication(Vacancy $vacancy, array $stageStatuses = []): Application
    {
        $vacancy->load('workflowTemplateSnapshot.stages');

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => Candidate::factory()->create()->id,
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

    private function renderProfileView(Application $application, Vacancy $lowongan): string
    {
        $application = $this->app->make(BuildCandidateProfileData::class)->execute($application);

        return view('exports.candidate-profile', compact('application', 'lowongan'))->render();
    }

    public function test_hr_admin_can_download_candidate_pdf(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($this->hrAdmin())
            ->get(route('lowongan.kandidat.pdf', ['lowongan' => $vacancy, 'application' => $application]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_pdf_contains_candidate_personal_data(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy();
        $candidate = Candidate::factory()->create(['nama_lengkap' => 'Teguh Prasetyo', 'email' => 'teguh@example.com']);
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $candidate->id,
        ]);

        $vacancy->load('workflowTemplateSnapshot.stages');
        $snapshotStages = $vacancy->workflowTemplateSnapshot->stages->sortBy('position')->values();
        foreach ($snapshotStages as $index => $stage) {
            ApplicationStage::factory()->create([
                'application_id' => $application->id,
                'position' => $stage->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $index === 0 ? ApplicationStageStatus::Selesai : ($index === 1 ? ApplicationStageStatus::Aktif : ApplicationStageStatus::Pending),
            ]);
        }

        $html = $this->renderProfileView($application, $vacancy);

        $this->assertStringContainsString('Teguh Prasetyo', $html);
        $this->assertStringContainsString('teguh@example.com', $html);
    }

    public function test_pdf_contains_screening_section(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy, [
            ApplicationStageStatus::Selesai,
            ApplicationStageStatus::Aktif,
            ApplicationStageStatus::Pending,
        ]);

        $application->load('stages');
        $screeningStage = $application->stages->firstWhere('key', 'skrining_cv_hr');
        $screeningStage->update(['catatan' => 'Kandidat sangat menjanjikan']);

        $html = $this->renderProfileView($application, $vacancy);

        $this->assertStringContainsString('Skrining CV', $html);
        $this->assertStringContainsString('Kandidat sangat menjanjikan', $html);
    }

    public function test_pdf_contains_disc_results_when_available(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        $discSubmission = DiscSubmission::factory()->create([
            'application_id' => $application->id,
            'submitted_at' => now(),
        ]);

        DiscResult::factory()->create([
            'disc_submission_id' => $discSubmission->id,
        ]);

        $html = $this->renderProfileView($application, $vacancy);

        $this->assertStringContainsString('DiSC', $html);
        $this->assertStringContainsString('Tipe Primer', $html);
    }

    public function test_pdf_contains_mbti_results_when_available(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        $mbtiSubmission = MbtiSubmission::factory()->create([
            'application_id' => $application->id,
            'submitted_at' => now(),
        ]);

        MbtiResult::factory()->create([
            'mbti_submission_id' => $mbtiSubmission->id,
            'tipe' => 'INTJ',
        ]);

        $html = $this->renderProfileView($application, $vacancy);

        $this->assertStringContainsString('MBTI', $html);
        $this->assertStringContainsString('INTJ', $html);
    }

    public function test_pdf_contains_mcu_status_when_available(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        McuResult::factory()->create([
            'application_id' => $application->id,
            'catatan' => 'MCU selesai dan dinyatakan sehat',
        ]);

        $html = $this->renderProfileView($application, $vacancy);

        $this->assertStringContainsString('MCU', $html);
        $this->assertStringContainsString('MCU selesai dan dinyatakan sehat', $html);
    }

    public function test_pdf_contains_timeline_section(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        $html = $this->renderProfileView($application, $vacancy);

        $this->assertStringContainsString('Timeline', $html);
        $this->assertStringContainsString('Pendaftaran', $html);
    }

    public function test_non_hr_admin_cannot_download_pdf(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        foreach ([Role::HrManager, Role::UnitHead, Role::Director, Role::Employee] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('lowongan.kandidat.pdf', ['lowongan' => $vacancy, 'application' => $application]))
                ->assertForbidden();
        }
    }

    public function test_returns_404_when_application_belongs_to_different_vacancy(): void
    {
        $this->seedStages();

        $vacancy1 = $this->createVacancy();
        $vacancy2 = $this->createVacancy();
        $application = $this->makeApplication($vacancy2);

        $this->actingAs($this->hrAdmin())
            ->get(route('lowongan.kandidat.pdf', ['lowongan' => $vacancy1, 'application' => $application]))
            ->assertNotFound();
    }
}
