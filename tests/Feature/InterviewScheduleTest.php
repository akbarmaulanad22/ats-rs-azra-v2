<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Mail\TemplatedMail;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\Employee;
use App\Models\Stage;
use App\Models\Unit;
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

    private function createVacancyWithStages(array $stageKeys, ?Unit $unit = null): Vacancy
    {
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->published()->create([
            'unit_id' => $unit?->id ?? Unit::factory()->create()->id,
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

    private function makeUnitHead(Unit $unit): User
    {
        $user = User::factory()->create(['role' => Role::UnitHead, 'is_active' => true]);
        Employee::factory()->create(['user_id' => $user->id, 'unit' => $unit->nama]);

        return $user;
    }

    private function makeEmployee(Unit $unit): User
    {
        $user = User::factory()->create(['role' => Role::Employee, 'is_active' => true]);
        Employee::factory()->create(['user_id' => $user->id, 'unit' => $unit->nama]);

        return $user;
    }

    public function test_hr_admin_can_schedule_wawancara_user_interview(): void
    {
        Mail::fake();
        Notification::fake();
        $this->seedStages();
        $this->seedEmailTemplates();

        $unit = Unit::factory()->create();
        $admin = User::factory()->hrAdmin()->create();
        $interviewer = $this->makeUnitHead($unit);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting Lt. 3',
                'interviewer_id' => $interviewer->id,
            ]
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        $stage = $application->stages()->where('key', 'wawancara_user')->first();
        $this->assertNotNull($stage->jadwal);
        $this->assertEquals('Ruang Meeting Lt. 3', $stage->lokasi);
        $this->assertEquals($interviewer->id, $stage->interviewer_id);

        Mail::assertQueued(TemplatedMail::class, 1);
        Notification::assertSentTo($interviewer, WawancaraDijadwalkan::class);
    }

    public function test_only_assigned_interviewer_notified_not_all_unit_heads(): void
    {
        Notification::fake();
        Mail::fake();
        $this->seedStages();
        $this->seedEmailTemplates();

        $unit = Unit::factory()->create();
        $admin = User::factory()->hrAdmin()->create();
        $assignedInterviewer = $this->makeUnitHead($unit);
        $otherUnitHead = $this->makeUnitHead($unit);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting',
                'interviewer_id' => $assignedInterviewer->id,
            ]
        );

        Notification::assertSentTo($assignedInterviewer, WawancaraDijadwalkan::class);
        Notification::assertNotSentTo($otherUnitHead, WawancaraDijadwalkan::class);
    }

    public function test_hr_manager_can_schedule_wawancara_user_interview(): void
    {
        Mail::fake();
        Notification::fake();
        $this->seedStages();
        $this->seedEmailTemplates();

        $unit = Unit::factory()->create();
        $manager = User::factory()->create(['role' => Role::HrManager]);
        $interviewer = $this->makeUnitHead($unit);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $response = $this->actingAs($manager)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting Lt. 2',
                'interviewer_id' => $interviewer->id,
            ]
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));
    }

    public function test_unit_head_cannot_schedule_interview(): void
    {
        $this->seedStages();

        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting',
                'interviewer_id' => $unitHead->id,
            ]
        );

        $response->assertStatus(403);
    }

    public function test_employee_cannot_schedule_interview(): void
    {
        $this->seedStages();

        $unit = Unit::factory()->create();
        $employee = $this->makeEmployee($unit);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $response = $this->actingAs($employee)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting',
                'interviewer_id' => $employee->id,
            ]
        );

        $response->assertStatus(403);
    }

    public function test_scheduling_rejects_interviewer_from_different_unit(): void
    {
        $this->seedStages();
        $this->seedEmailTemplates();

        $unit = Unit::factory()->create();
        $otherUnit = Unit::factory()->create();
        $admin = User::factory()->hrAdmin()->create();
        $wrongInterviewer = $this->makeUnitHead($otherUnit);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting',
                'interviewer_id' => $wrongInterviewer->id,
            ]
        );

        $response->assertSessionHasErrors('interviewer_id');
    }

    public function test_scheduling_rejects_inactive_interviewer(): void
    {
        $this->seedStages();

        $unit = Unit::factory()->create();
        $admin = User::factory()->hrAdmin()->create();
        $inactiveUser = User::factory()->create(['role' => Role::UnitHead, 'is_active' => false]);
        Employee::factory()->create(['user_id' => $inactiveUser->id, 'unit' => $unit->nama]);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting',
                'interviewer_id' => $inactiveUser->id,
            ]
        );

        $response->assertSessionHasErrors('interviewer_id');
    }

    public function test_cannot_schedule_if_no_active_wawancara_stage(): void
    {
        $this->seedStages();

        $admin = User::factory()->hrAdmin()->create();

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding']);
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

        $unit = Unit::factory()->create();
        $admin = User::factory()->hrAdmin()->create();
        $interviewer = $this->makeUnitHead($unit);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting Lt. 3',
                'interviewer_id' => $interviewer->id,
            ]
        );

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(5)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Lain',
                'interviewer_id' => $interviewer->id,
            ]
        );

        $response->assertSessionHasErrors('jadwal');
    }

    public function test_validation_requires_fields(): void
    {
        $this->seedStages();

        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            []
        );

        $response->assertSessionHasErrors(['jadwal', 'lokasi', 'interviewer_id']);
    }

    public function test_jadwal_must_be_future_date(): void
    {
        $this->seedStages();

        $unit = Unit::factory()->create();
        $admin = User::factory()->hrAdmin()->create();
        $interviewer = $this->makeUnitHead($unit);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$vacancy, $application]),
            [
                'jadwal' => now()->subDay()->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting',
                'interviewer_id' => $interviewer->id,
            ]
        );

        $response->assertSessionHasErrors('jadwal');
    }

    public function test_returns_404_for_mismatched_vacancy(): void
    {
        $this->seedStages();

        $admin = User::factory()->hrAdmin()->create();

        $vacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding']);
        $otherVacancy = $this->createVacancyWithStages(['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $response = $this->actingAs($admin)->post(
            route('lowongan.wawancara.jadwal', [$otherVacancy, $application]),
            [
                'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Meeting',
            ]
        );

        $response->assertStatus(404);
    }

    public function test_hr_admin_can_reschedule_wawancara_user(): void
    {
        Mail::fake();
        Notification::fake();
        $this->seedStages();
        $this->seedEmailTemplates();

        $unit = Unit::factory()->create();
        $admin = User::factory()->hrAdmin()->create();
        $interviewer = $this->makeUnitHead($unit);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $application->stages()->where('key', 'wawancara_user')->update([
            'jadwal' => now()->addDays(2),
            'lokasi' => 'Ruang Lama',
            'interviewer_id' => $interviewer->id,
        ]);

        $response = $this->actingAs($admin)->put(
            route('lowongan.wawancara.jadwal.update', [$vacancy, $application]),
            [
                'jadwal' => now()->addDays(5)->format('Y-m-d\TH:i'),
                'lokasi' => 'Ruang Baru',
                'interviewer_id' => $interviewer->id,
            ]
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        $stage = $application->stages()->where('key', 'wawancara_user')->first();
        $this->assertEquals('Ruang Baru', $stage->lokasi);

        Mail::assertQueued(TemplatedMail::class, 1);
        Notification::assertSentTo($interviewer, WawancaraDijadwalkan::class);
    }

    public function test_reassign_notifies_new_interviewer_not_candidate(): void
    {
        Mail::fake();
        Notification::fake();
        $this->seedStages();
        $this->seedEmailTemplates();

        $unit = Unit::factory()->create();
        $admin = User::factory()->hrAdmin()->create();
        $oldInterviewer = $this->makeUnitHead($unit);
        $newInterviewer = $this->makeEmployee($unit);

        $vacancy = $this->createVacancyWithStages(['lamaran', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplicationAtStage($vacancy, 'wawancara_user');

        $jadwal = now()->addDays(3)->format('Y-m-d\TH:i');

        $application->stages()->where('key', 'wawancara_user')->update([
            'jadwal' => $jadwal,
            'lokasi' => 'Ruang Meeting',
            'interviewer_id' => $oldInterviewer->id,
        ]);

        $response = $this->actingAs($admin)->put(
            route('lowongan.wawancara.jadwal.update', [$vacancy, $application]),
            [
                'jadwal' => $jadwal,
                'lokasi' => 'Ruang Meeting',
                'interviewer_id' => $newInterviewer->id,
            ]
        );

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        Mail::assertNothingQueued();
        Notification::assertSentTo($newInterviewer, WawancaraDijadwalkan::class);
        Notification::assertNotSentTo($oldInterviewer, WawancaraDijadwalkan::class);
    }
}
