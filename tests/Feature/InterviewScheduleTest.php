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
use App\Notifications\WawancaraDijadwalkan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InterviewScheduleTest extends TestCase
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

    private function createVacancyWithStages(array $stageKeys): Vacancy
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

    private function makeApplicationAtStage(Vacancy $vacancy, string $activeStageKey): Application
    {
        $vacancy->load('workflowTemplateSnapshot.stages');

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => Candidate::factory()->create()->id,
        ]);

        $snapshotStages = $vacancy->workflowTemplateSnapshot->stages->sortBy('position')->values();

        foreach ($snapshotStages as $stage) {
            $status = match (true) {
                $stage->key === $activeStageKey => ApplicationStageStatus::Aktif,
                $stage->position < $snapshotStages->firstWhere('key', $activeStageKey)->position => ApplicationStageStatus::Selesai,
                default => ApplicationStageStatus::Pending,
            };

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

    public function test_hr_admin_can_schedule_interview(): void
    {
        Mail::fake();
        Notification::fake();
        $this->seedStages();
        $this->seedEmailTemplates();

        $admin = User::factory()->hrAdmin()->create();
        $unitHead = User::factory()->create(['role' => Role::UnitHead]);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_kepala_unit', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting Lt. 3',
            ]
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        $stage = $application->stages()->where('key', 'wawancara_kepala_unit')->first();
        $this->assertNotNull($stage->jadwal);
        $this->assertEquals('Ruang Meeting Lt. 3', $stage->lokasi);

        Mail::assertQueued(TemplatedMail::class, 1);
        Notification::assertSentTo($unitHead, WawancaraDijadwalkan::class);
    }

    public function test_hr_manager_can_schedule_interview(): void
    {
        Mail::fake();
        Notification::fake();
        $this->seedStages();
        $this->seedEmailTemplates();

        $manager = User::factory()->create(['role' => Role::HrManager]);
        User::factory()->create(['role' => Role::UnitHead]);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_kepala_unit', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($manager)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting Lt. 2',
            ]
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));
    }

    public function test_unit_head_cannot_schedule_interview(): void
    {
        $this->seedStages();

        $unitHead = User::factory()->create(['role' => Role::UnitHead]);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_kepala_unit', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting',
            ]
        );

        $response->assertStatus(403);
    }

    public function test_cannot_schedule_if_no_active_wawancara_stage(): void
    {
        $this->seedStages();

        $admin = User::factory()->hrAdmin()->create();

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_kepala_unit', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'skrining_cv_hr');

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting',
            ]
        );

        $response->assertSessionHasErrors('jadwal');
    }

    public function test_cannot_schedule_twice(): void
    {
        Mail::fake();
        Notification::fake();
        $this->seedStages();
        $this->seedEmailTemplates();

        $admin = User::factory()->hrAdmin()->create();
        User::factory()->create(['role' => Role::UnitHead]);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_kepala_unit', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_kepala_unit');

        $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting Lt. 3',
            ]
        );

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(5)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Lain',
            ]
        );

        $response->assertSessionHasErrors('jadwal');
    }

    public function test_validation_requires_fields(): void
    {
        $this->seedStages();

        $admin = User::factory()->hrAdmin()->create();

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_kepala_unit', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            []
        );

        $response->assertSessionHasErrors(['jadwal', 'lokasi']);
    }

    public function test_jadwal_must_be_future_date(): void
    {
        $this->seedStages();

        $admin = User::factory()->hrAdmin()->create();

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_kepala_unit', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->subDay()->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting',
            ]
        );

        $response->assertSessionHasErrors('jadwal');
    }

    public function test_returns_404_for_mismatched_vacancy(): void
    {
        $this->seedStages();

        $admin = User::factory()->hrAdmin()->create();

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_kepala_unit', 'onboarding']);
        $otherVacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_kepala_unit', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$otherVacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting',
            ]
        );

        $response->assertStatus(404);
    }
}
