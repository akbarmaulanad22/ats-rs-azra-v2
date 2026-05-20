<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\OfferingLetterStatus;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\OfferingLetter;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use App\Notifications\PenawaranDirespon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OfferingResponseTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    private function createVacancy(array $stageKeys): Vacancy
    {
        $unit = Unit::factory()->create();
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->published()->create([
            'unit_id' => $unit->id,
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);
    }

    private function makeApplicationAtStage(Vacancy $vacancy, string $activeStageKey): Application
    {
        $vacancy->load('workflowTemplateSnapshot.stages');
        $snapshotStages = $vacancy->workflowTemplateSnapshot->stages->sortBy('position')->values();

        $targetPosition = $snapshotStages->search(fn ($s) => $s->key === $activeStageKey);

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => Candidate::factory()->create()->id,
        ]);

        foreach ($snapshotStages as $index => $stage) {
            $status = $index < $targetPosition
                ? ApplicationStageStatus::Selesai
                : ($index === $targetPosition ? ApplicationStageStatus::Aktif : ApplicationStageStatus::Pending);

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

    private function createOfferingWithSignedUrls(Application $application): array
    {
        $offering = OfferingLetter::create([
            'application_id' => $application->id,
            'jabatan_ditawarkan' => 'Perawat',
            'gaji' => 'Rp 5.000.000',
            'tanggal_mulai' => now()->addDays(14)->toDateString(),
            'sent_at' => now(),
            'status' => 'pending',
        ]);

        $expiry = now()->addDays(7);

        return [
            'offering' => $offering,
            'acceptUrl' => URL::temporarySignedRoute('offering.accept', $expiry, ['offering' => $offering->id]),
            'rejectUrl' => URL::temporarySignedRoute('offering.reject', $expiry, ['offering' => $offering->id]),
        ];
    }

    // ── Accept ───────────────────────────────────────────────────────────────

    public function test_candidate_sees_accept_form_on_get(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');
        $data = $this->createOfferingWithSignedUrls($application);

        $response = $this->get($data['acceptUrl']);

        $response->assertOk();
        $response->assertViewIs('offering.accept-form');
    }

    public function test_candidate_can_accept_offering_via_signed_link(): void
    {
        $this->seedStages();
        Notification::fake();

        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');
        $data = $this->createOfferingWithSignedUrls($application);

        $response = $this->post($data['acceptUrl']);

        $response->assertOk();
        $response->assertViewIs('offering.accepted');

        $this->assertDatabaseHas('offering_letters', [
            'id' => $data['offering']->id,
            'status' => OfferingLetterStatus::Accepted,
        ]);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'surat_penawaran',
            'status' => ApplicationStageStatus::Selesai,
        ]);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'mcu',
            'status' => ApplicationStageStatus::Aktif,
        ]);
    }

    public function test_accept_sends_notification_to_hr_admins(): void
    {
        $this->seedStages();
        Notification::fake();

        $hrAdmin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');
        $data = $this->createOfferingWithSignedUrls($application);

        $this->post($data['acceptUrl']);

        Notification::assertSentTo($hrAdmin, PenawaranDirespon::class);
    }

    // ── Reject ───────────────────────────────────────────────────────────────

    public function test_candidate_sees_reject_form_on_get(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');
        $data = $this->createOfferingWithSignedUrls($application);

        $response = $this->get($data['rejectUrl']);

        $response->assertOk();
        $response->assertViewIs('offering.reject-form');
    }

    public function test_candidate_can_reject_offering_with_reason(): void
    {
        $this->seedStages();
        Notification::fake();

        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');
        $data = $this->createOfferingWithSignedUrls($application);

        $response = $this->post($data['rejectUrl'], [
            'rejection_reason' => 'Sudah menerima tawaran lain.',
        ]);

        $response->assertOk();
        $response->assertViewIs('offering.rejected');

        $this->assertDatabaseHas('offering_letters', [
            'id' => $data['offering']->id,
            'status' => OfferingLetterStatus::Rejected,
            'rejection_reason' => 'Sudah menerima tawaran lain.',
        ]);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'surat_penawaran',
            'status' => ApplicationStageStatus::Gagal,
        ]);
    }

    public function test_candidate_can_reject_without_reason(): void
    {
        $this->seedStages();
        Notification::fake();

        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');
        $data = $this->createOfferingWithSignedUrls($application);

        $response = $this->post($data['rejectUrl']);

        $response->assertOk();

        $this->assertDatabaseHas('offering_letters', [
            'id' => $data['offering']->id,
            'status' => OfferingLetterStatus::Rejected,
            'rejection_reason' => null,
        ]);
    }

    public function test_reject_sends_notification_to_hr_admins(): void
    {
        $this->seedStages();
        Notification::fake();

        $hrAdmin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');
        $data = $this->createOfferingWithSignedUrls($application);

        $this->post($data['rejectUrl']);

        Notification::assertSentTo($hrAdmin, PenawaranDirespon::class);
    }

    // ── Already responded ────────────────────────────────────────────────────

    public function test_cannot_respond_twice(): void
    {
        $this->seedStages();
        Notification::fake();

        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');
        $data = $this->createOfferingWithSignedUrls($application);

        $this->post($data['acceptUrl']);

        $response = $this->post($data['acceptUrl']);
        $response->assertOk();
        $response->assertViewIs('offering.already-responded');

        $response = $this->post($data['rejectUrl']);
        $response->assertOk();
        $response->assertViewIs('offering.already-responded');
    }

    // ── Unsigned / expired ───────────────────────────────────────────────────

    public function test_unsigned_link_returns_403(): void
    {
        $this->seedStages();

        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');
        $offering = OfferingLetter::create([
            'application_id' => $application->id,
            'jabatan_ditawarkan' => 'Perawat',
            'gaji' => 'Rp 5.000.000',
            'tanggal_mulai' => now()->addDays(14)->toDateString(),
            'sent_at' => now(),
            'status' => 'pending',
        ]);

        $response = $this->get(route('offering.accept', ['offering' => $offering->id]));

        $response->assertForbidden();
    }
}
