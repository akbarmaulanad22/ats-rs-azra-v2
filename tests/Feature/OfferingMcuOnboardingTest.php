<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\McuStatus;
use App\Enums\Role;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\McuResult;
use App\Models\OfferingLetter;
use App\Models\OnboardingResult;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfferingMcuOnboardingTest extends TestCase
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

    private function makeHrAdmin(): User
    {
        return User::factory()->hrAdmin()->create();
    }

    private function makeNonAdmin(): User
    {
        return User::factory()->withRole(Role::Employee)->create();
    }

    // ── Offering Letter ───────────────────────────────────────────────────────

    public function test_hr_admin_can_view_offering_letter_page(): void
    {
        $this->seedStages();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');

        $response = $this->actingAs($admin)->get(route('lowongan.surat-penawaran.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertViewIs('offering-letter.show');
    }

    public function test_non_admin_cannot_view_offering_letter_page(): void
    {
        $this->seedStages();
        $user = $this->makeNonAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');

        $response = $this->actingAs($user)->get(route('lowongan.surat-penawaran.show', [$vacancy, $application]));

        $response->assertForbidden();
    }

    public function test_hr_admin_can_send_offering_letter(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');

        $response = $this->actingAs($admin)->post(route('lowongan.surat-penawaran.kirim', [$vacancy, $application]), [
            'jabatan_ditawarkan' => 'Perawat Rawat Inap',
            'gaji' => 'Rp 5.000.000',
            'tanggal_mulai' => now()->addDays(14)->toDateString(),
            'catatan' => 'Harap konfirmasi kehadiran.',
        ]);

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        $this->assertDatabaseHas('offering_letters', [
            'application_id' => $application->id,
            'jabatan_ditawarkan' => 'Perawat Rawat Inap',
            'gaji' => 'Rp 5.000.000',
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

    public function test_offering_letter_sent_at_is_recorded(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');

        $this->actingAs($admin)->post(route('lowongan.surat-penawaran.kirim', [$vacancy, $application]), [
            'jabatan_ditawarkan' => 'Dokter Umum',
            'gaji' => 'Rp 8.000.000',
            'tanggal_mulai' => now()->addDays(7)->toDateString(),
        ]);

        $offering = OfferingLetter::where('application_id', $application->id)->first();
        $this->assertNotNull($offering->sent_at);
    }

    public function test_cannot_send_offering_letter_twice(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');

        $payload = [
            'jabatan_ditawarkan' => 'Perawat',
            'gaji' => 'Rp 4.000.000',
            'tanggal_mulai' => now()->addDays(7)->toDateString(),
        ];

        $this->actingAs($admin)->post(route('lowongan.surat-penawaran.kirim', [$vacancy, $application]), $payload);

        $application->refresh();
        $response = $this->actingAs($admin)->post(route('lowongan.surat-penawaran.kirim', [$vacancy, $application]), $payload);

        $response->assertSessionHasErrors(['offering']);
    }

    public function test_offering_letter_requires_valid_fields(): void
    {
        $this->seedStages();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');

        $response = $this->actingAs($admin)->post(route('lowongan.surat-penawaran.kirim', [$vacancy, $application]), []);

        $response->assertSessionHasErrors(['jabatan_ditawarkan', 'gaji', 'tanggal_mulai']);
    }

    // ── MCU ───────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_mcu_page(): void
    {
        $this->seedStages();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $response = $this->actingAs($admin)->get(route('lowongan.mcu.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertViewIs('mcu.show');
    }

    public function test_non_admin_cannot_view_mcu_page(): void
    {
        $this->seedStages();
        $user = $this->makeNonAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $response = $this->actingAs($user)->get(route('lowongan.mcu.show', [$vacancy, $application]));

        $response->assertForbidden();
    }

    public function test_hr_admin_can_update_mcu_status_to_scheduled(): void
    {
        $this->seedStages();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $response = $this->actingAs($admin)->post(route('lowongan.mcu.status', [$vacancy, $application]), [
            'status' => McuStatus::Selesai->value,
            'catatan' => 'MCU dilakukan di RS Azra.',
        ]);

        $response->assertRedirect(route('lowongan.mcu.show', [$vacancy, $application]));

        $this->assertDatabaseHas('mcu_results', [
            'application_id' => $application->id,
            'status' => McuStatus::Selesai->value,
        ]);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'mcu',
            'status' => ApplicationStageStatus::Aktif,
        ]);
    }

    public function test_mcu_lulus_advances_candidate_to_onboarding(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $this->actingAs($admin)->post(route('lowongan.mcu.status', [$vacancy, $application]), [
            'status' => McuStatus::Lulus->value,
        ]);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'mcu',
            'status' => ApplicationStageStatus::Selesai,
        ]);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'onboarding',
            'status' => ApplicationStageStatus::Aktif,
        ]);
    }

    public function test_mcu_tidak_lulus_rejects_candidate(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $this->actingAs($admin)->post(route('lowongan.mcu.status', [$vacancy, $application]), [
            'status' => McuStatus::TidakLulus->value,
        ]);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'mcu',
            'status' => ApplicationStageStatus::Gagal,
        ]);
    }

    public function test_hr_admin_can_upload_mcu_document(): void
    {
        $this->seedStages();
        Storage::fake('public');
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $file = UploadedFile::fake()->create('mcu-result.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('lowongan.mcu.dokumen', [$vacancy, $application]), [
            'dokumen' => $file,
        ]);

        $response->assertRedirect(route('lowongan.mcu.show', [$vacancy, $application]));

        $mcuResult = McuResult::where('application_id', $application->id)->first();
        $this->assertNotNull($mcuResult);
        $this->assertNotNull($mcuResult->dokumen_path);
        Storage::disk('public')->assertExists($mcuResult->dokumen_path);
    }

    public function test_mcu_document_upload_rejects_non_pdf(): void
    {
        $this->seedStages();
        Storage::fake('public');
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $file = UploadedFile::fake()->create('mcu-result.docx', 200, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($admin)->post(route('lowongan.mcu.dokumen', [$vacancy, $application]), [
            'dokumen' => $file,
        ]);

        $response->assertSessionHasErrors(['dokumen']);
    }

    public function test_mcu_document_upload_rejects_oversized_file(): void
    {
        $this->seedStages();
        Storage::fake('public');
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $file = UploadedFile::fake()->create('mcu-result.pdf', 4096, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('lowongan.mcu.dokumen', [$vacancy, $application]), [
            'dokumen' => $file,
        ]);

        $response->assertSessionHasErrors(['dokumen']);
    }

    public function test_candidate_can_upload_mcu_document_via_token(): void
    {
        $this->seedStages();
        Storage::fake('public');
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $file = UploadedFile::fake()->create('my-mcu.pdf', 1024, 'application/pdf');

        $response = $this->post(route('kandidat.mcu.upload.store', $application->token), [
            'dokumen' => $file,
        ]);

        $response->assertRedirect(route('kandidat.mcu.upload', $application->token));

        $mcuResult = McuResult::where('application_id', $application->id)->first();
        $this->assertNotNull($mcuResult);
        Storage::disk('public')->assertExists($mcuResult->dokumen_path);
    }

    public function test_candidate_cannot_upload_mcu_when_stage_not_active(): void
    {
        $this->seedStages();
        Storage::fake('public');
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'onboarding');

        $file = UploadedFile::fake()->create('my-mcu.pdf', 500, 'application/pdf');

        $response = $this->post(route('kandidat.mcu.upload.store', $application->token), [
            'dokumen' => $file,
        ]);

        $response->assertNotFound();
    }

    public function test_candidate_mcu_upload_replaces_previous_document(): void
    {
        $this->seedStages();
        Storage::fake('public');
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $firstFile = UploadedFile::fake()->create('first.pdf', 500, 'application/pdf');
        $this->post(route('kandidat.mcu.upload.store', $application->token), ['dokumen' => $firstFile]);

        $firstPath = McuResult::where('application_id', $application->id)->first()->dokumen_path;

        $secondFile = UploadedFile::fake()->create('second.pdf', 500, 'application/pdf');
        $this->post(route('kandidat.mcu.upload.store', $application->token), ['dokumen' => $secondFile]);

        $mcuResult = McuResult::where('application_id', $application->id)->first();
        $this->assertNotEquals($firstPath, $mcuResult->dokumen_path);
        Storage::disk('public')->assertMissing($firstPath);
    }

    // ── Onboarding ────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_onboarding_page(): void
    {
        $this->seedStages();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'onboarding');

        $response = $this->actingAs($admin)->get(route('lowongan.onboarding.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertViewIs('onboarding.show');
    }

    public function test_non_admin_cannot_view_onboarding_page(): void
    {
        $this->seedStages();
        $user = $this->makeNonAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'onboarding');

        $response = $this->actingAs($user)->get(route('lowongan.onboarding.show', [$vacancy, $application]));

        $response->assertForbidden();
    }

    public function test_hr_admin_can_send_onboarding_invitation(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'onboarding');

        $tanggalBergabung = now()->addDays(30)->toDateString();

        $response = $this->actingAs($admin)->post(route('lowongan.onboarding.undangan', [$vacancy, $application]), [
            'tanggal_bergabung' => $tanggalBergabung,
            'catatan' => 'Hadir pukul 08.00 WIB.',
        ]);

        $response->assertRedirect(route('lowongan.onboarding.show', [$vacancy, $application]));

        $onboardingRecord = OnboardingResult::where('application_id', $application->id)->first();
        $this->assertNotNull($onboardingRecord);
        $this->assertEquals($tanggalBergabung, $onboardingRecord->tanggal_bergabung->toDateString());

        $onboarding = OnboardingResult::where('application_id', $application->id)->first();
        $this->assertNotNull($onboarding->sent_at);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'onboarding',
            'status' => ApplicationStageStatus::Aktif,
        ]);
    }

    public function test_hr_admin_can_complete_onboarding(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'onboarding');

        $this->actingAs($admin)->post(route('lowongan.onboarding.undangan', [$vacancy, $application]), [
            'tanggal_bergabung' => now()->addDays(30)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->post(route('lowongan.onboarding.selesai', [$vacancy, $application]));

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'onboarding',
            'status' => ApplicationStageStatus::Selesai,
        ]);
    }

    public function test_cannot_complete_onboarding_twice(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'onboarding');

        $this->actingAs($admin)->post(route('lowongan.onboarding.selesai', [$vacancy, $application]));
        $response = $this->actingAs($admin)->post(route('lowongan.onboarding.selesai', [$vacancy, $application]));

        $response->assertSessionHasErrors(['onboarding']);
    }

    public function test_onboarding_requires_valid_join_date(): void
    {
        $this->seedStages();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'onboarding');

        $response = $this->actingAs($admin)->post(route('lowongan.onboarding.undangan', [$vacancy, $application]), [
            'tanggal_bergabung' => now()->subDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['tanggal_bergabung']);
    }
}
