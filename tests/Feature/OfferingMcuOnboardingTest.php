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

        $response = $this->actingAs($admin)->get(route('lowongan.pipeline.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertViewIs('vacancies.pipeline-show');
    }

    public function test_non_admin_cannot_view_offering_letter_page(): void
    {
        $this->seedStages();
        $user = $this->makeNonAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'surat_penawaran');

        $response = $this->actingAs($user)->get(route('lowongan.pipeline.show', [$vacancy, $application]));

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
            'status' => ApplicationStageStatus::Aktif,
        ]);

        $this->assertDatabaseHas('offering_letters', [
            'application_id' => $application->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'mcu',
            'status' => ApplicationStageStatus::Pending,
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

        $response = $this->actingAs($admin)->get(route('lowongan.pipeline.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertViewIs('vacancies.pipeline-show');
    }

    public function test_non_admin_cannot_view_mcu_page(): void
    {
        $this->seedStages();
        $user = $this->makeNonAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $response = $this->actingAs($user)->get(route('lowongan.pipeline.show', [$vacancy, $application]));

        $response->assertForbidden();
    }

    public function test_hr_admin_can_schedule_mcu(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $response = $this->actingAs($admin)->post(route('lowongan.mcu.jadwal', [$vacancy, $application]), [
            'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'lokasi' => 'RS Azra Lt. 2',
        ]);

        $response->assertRedirect(route('lowongan.pipeline', $vacancy));

        $stage = $application->stages()->where('key', 'mcu')->first();
        $this->assertNotNull($stage->jadwal);
        $this->assertEquals('RS Azra Lt. 2', $stage->lokasi);
    }

    public function test_cannot_schedule_mcu_twice(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $this->actingAs($admin)->post(route('lowongan.mcu.jadwal', [$vacancy, $application]), [
            'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'lokasi' => 'RS Azra Lt. 2',
        ]);

        $response = $this->actingAs($admin)->post(route('lowongan.mcu.jadwal', [$vacancy, $application]), [
            'jadwal' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'lokasi' => 'Lokasi Lain',
        ]);

        $response->assertSessionHasErrors('jadwal');
    }

    public function test_mcu_schedule_validation_requires_fields(): void
    {
        $this->seedStages();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $response = $this->actingAs($admin)->post(route('lowongan.mcu.jadwal', [$vacancy, $application]), []);

        $response->assertSessionHasErrors(['jadwal', 'lokasi']);
    }

    public function test_mcu_lulus_advances_candidate_to_onboarding(): void
    {
        $this->seedStages();
        Mail::fake();
        Storage::fake('public');
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $application->stages()->where('key', 'mcu')->update([
            'jadwal' => now()->addDays(2),
            'lokasi' => 'RS Azra',
        ]);

        $file = UploadedFile::fake()->create('mcu-result.pdf', 500, 'application/pdf');

        $this->actingAs($admin)->post(route('lowongan.mcu.keputusan', [$vacancy, $application]), [
            'keputusan' => McuStatus::Lulus->value,
            'dokumen' => $file,
            'catatan' => 'Hasil MCU baik.',
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

        $mcuResult = McuResult::where('application_id', $application->id)->first();
        $this->assertNotNull($mcuResult);
        $this->assertEquals(McuStatus::Lulus, $mcuResult->keputusan);
        $this->assertEquals($admin->id, $mcuResult->reviewer_id);
        $this->assertNotNull($mcuResult->submitted_at);
        $this->assertNotNull($mcuResult->dokumen_path);
        Storage::disk('public')->assertExists($mcuResult->dokumen_path);
    }

    public function test_mcu_tidak_lulus_rejects_candidate(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $application->stages()->where('key', 'mcu')->update([
            'jadwal' => now()->addDays(2),
            'lokasi' => 'RS Azra',
        ]);

        $this->actingAs($admin)->post(route('lowongan.mcu.keputusan', [$vacancy, $application]), [
            'keputusan' => McuStatus::TidakLulus->value,
        ]);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'mcu',
            'status' => ApplicationStageStatus::Gagal,
        ]);
    }

    public function test_mcu_ditangguhkan_reserves_candidate(): void
    {
        $this->seedStages();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $application->stages()->where('key', 'mcu')->update([
            'jadwal' => now()->addDays(2),
            'lokasi' => 'RS Azra',
        ]);

        $this->actingAs($admin)->post(route('lowongan.mcu.keputusan', [$vacancy, $application]), [
            'keputusan' => McuStatus::Ditangguhkan->value,
            'catatan' => 'Perlu pemeriksaan tambahan.',
        ]);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $application->id,
            'key' => 'mcu',
            'status' => ApplicationStageStatus::Reserved,
        ]);
    }

    public function test_cannot_submit_mcu_result_twice(): void
    {
        $this->seedStages();
        Mail::fake();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $application->stages()->where('key', 'mcu')->update([
            'jadwal' => now()->addDays(2),
            'lokasi' => 'RS Azra',
        ]);

        $this->actingAs($admin)->post(route('lowongan.mcu.keputusan', [$vacancy, $application]), [
            'keputusan' => McuStatus::Lulus->value,
        ]);

        $application->load('stages');
        $application->stages()->where('key', 'mcu')->update(['status' => ApplicationStageStatus::Aktif]);

        $response = $this->actingAs($admin)->post(route('lowongan.mcu.keputusan', [$vacancy, $application]), [
            'keputusan' => McuStatus::TidakLulus->value,
        ]);

        $response->assertSessionHasErrors('mcu');
    }

    public function test_mcu_keputusan_validation_requires_keputusan(): void
    {
        $this->seedStages();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'mcu');

        $response = $this->actingAs($admin)->post(route('lowongan.mcu.keputusan', [$vacancy, $application]), []);

        $response->assertSessionHasErrors(['keputusan']);
    }

    // ── Onboarding ────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_onboarding_page(): void
    {
        $this->seedStages();
        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'onboarding');

        $response = $this->actingAs($admin)->get(route('lowongan.pipeline.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertViewIs('vacancies.pipeline-show');
    }

    public function test_non_admin_cannot_view_onboarding_page(): void
    {
        $this->seedStages();
        $user = $this->makeNonAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplicationAtStage($vacancy, 'onboarding');

        $response = $this->actingAs($user)->get(route('lowongan.pipeline.show', [$vacancy, $application]));

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

        $response->assertRedirect(route('lowongan.pipeline.show', [$vacancy, $application]));

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
