<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Mail\TemplatedMail;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\Stage;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplicationPipelineTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    private function seedEmailTemplates(): void
    {
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    private function createVacancyWithStages(array $stageKeys = ['aplikasi', 'skrining_cv_hr', 'onboarding']): Vacancy
    {
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->published()->create([
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

    // ── Advance (pass) ────────────────────────────────────────────────────────

    public function test_hr_admin_can_advance_application_to_next_stage(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->post(
            route('lowongan.lamaran.lanjut', [$vacancy, $application])
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        $application->load('stages');
        $skriningStage = $application->stages->firstWhere('key', 'skrining_cv_hr');
        $onboardingStage = $application->stages->firstWhere('key', 'onboarding');

        $this->assertEquals(ApplicationStageStatus::Selesai, $skriningStage->status);
        $this->assertEquals(ApplicationStageStatus::Aktif, $onboardingStage->status);
    }

    public function test_advance_sends_stage_transition_email(): void
    {
        Mail::fake();
        $this->seedStages();
        $this->seedEmailTemplates();

        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy);

        $this->actingAs($admin)->post(
            route('lowongan.lamaran.lanjut', [$vacancy, $application])
        );

        Mail::assertQueued(TemplatedMail::class, fn ($mail) => $mail->key === 'transisi_tahap');
    }

    public function test_reserved_candidate_can_be_advanced(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Reserved,
            2 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->post(
            route('lowongan.lamaran.lanjut', [$vacancy, $application])
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        $application->load('stages');
        $skriningStage = $application->stages->firstWhere('key', 'skrining_cv_hr');
        $onboardingStage = $application->stages->firstWhere('key', 'onboarding');

        $this->assertEquals(ApplicationStageStatus::Selesai, $skriningStage->status);
        $this->assertEquals(ApplicationStageStatus::Aktif, $onboardingStage->status);
    }

    public function test_advance_on_last_stage_completes_pipeline(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Aktif,
        ]);

        $response = $this->actingAs($admin)->post(
            route('lowongan.lamaran.lanjut', [$vacancy, $application])
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        $application->load('stages');
        $onboardingStage = $application->stages->firstWhere('key', 'onboarding');
        $this->assertEquals(ApplicationStageStatus::Selesai, $onboardingStage->status);
    }

    public function test_advance_fails_when_no_active_stage_exists(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Selesai,
        ]);

        $response = $this->actingAs($admin)->post(
            route('lowongan.lamaran.lanjut', [$vacancy, $application])
        );

        $response->assertSessionHasErrors('pipeline');
    }

    // ── Fail / reject ─────────────────────────────────────────────────────────

    public function test_hr_admin_can_fail_application(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->post(
            route('lowongan.lamaran.gagal', [$vacancy, $application])
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        $application->load('stages');
        $skriningStage = $application->stages->firstWhere('key', 'skrining_cv_hr');
        $this->assertEquals(ApplicationStageStatus::Gagal, $skriningStage->status);
    }

    public function test_fail_sends_rejection_email(): void
    {
        Mail::fake();
        $this->seedStages();
        $this->seedEmailTemplates();

        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy);

        $this->actingAs($admin)->post(
            route('lowongan.lamaran.gagal', [$vacancy, $application])
        );

        Mail::assertQueued(TemplatedMail::class, fn ($mail) => $mail->key === 'kandidat_ditolak');
    }

    public function test_reserved_candidate_can_be_rejected(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Reserved,
            2 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->post(
            route('lowongan.lamaran.gagal', [$vacancy, $application])
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        $application->load('stages');
        $skriningStage = $application->stages->firstWhere('key', 'skrining_cv_hr');
        $this->assertEquals(ApplicationStageStatus::Gagal, $skriningStage->status);
    }

    public function test_fail_fails_when_no_active_stage_exists(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Gagal,
            2 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->post(
            route('lowongan.lamaran.gagal', [$vacancy, $application])
        );

        $response->assertSessionHasErrors('pipeline');
    }

    // ── Authorization ─────────────────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_advance(): void
    {
        $this->seedStages();
        $vacancy = $this->createVacancyWithStages();
        $application = $this->makeApplication($vacancy);

        $response = $this->post(route('lowongan.lamaran.lanjut', [$vacancy, $application]));

        $response->assertRedirect(route('login'));
    }

    public function test_employee_cannot_advance_application(): void
    {
        $this->seedStages();
        $employee = User::factory()->create(['role' => Role::Employee]);
        $vacancy = $this->createVacancyWithStages();
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($employee)->post(
            route('lowongan.lamaran.lanjut', [$vacancy, $application])
        );

        $response->assertStatus(403);
    }

    public function test_employee_cannot_fail_application(): void
    {
        $this->seedStages();
        $employee = User::factory()->create(['role' => Role::Employee]);
        $vacancy = $this->createVacancyWithStages();
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($employee)->post(
            route('lowongan.lamaran.gagal', [$vacancy, $application])
        );

        $response->assertStatus(403);
    }

    public function test_hr_manager_can_advance_application(): void
    {
        $this->seedStages();
        $manager = User::factory()->withRole(Role::HrManager)->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($manager)->post(
            route('lowongan.lamaran.lanjut', [$vacancy, $application])
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));
    }
}
