<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Mail\TemplatedMail;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AutoRejectReservedTest extends TestCase
{
    use RefreshDatabase;

    private function seedEmailTemplates(): void
    {
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    private function createExpiredVacancyWithReservedApplication(): array
    {
        $vacancy = Vacancy::factory()->expired()->create();
        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        $stage = ApplicationStage::factory()->reserved()->create(['application_id' => $application->id]);

        return [$vacancy, $application, $stage];
    }

    public function test_auto_rejects_reserved_candidates_when_deadline_passed(): void
    {
        [, $application, $stage] = $this->createExpiredVacancyWithReservedApplication();

        $this->artisan('pipeline:auto-reject-reserved')->assertSuccessful();

        $this->assertDatabaseHas('application_stages', [
            'id' => $stage->id,
            'status' => ApplicationStageStatus::Gagal->value,
        ]);
    }

    public function test_sends_rejection_email_to_auto_rejected_candidates(): void
    {
        Mail::fake();
        $this->seedEmailTemplates();

        $this->createExpiredVacancyWithReservedApplication();

        $this->artisan('pipeline:auto-reject-reserved')->assertSuccessful();

        Mail::assertQueued(TemplatedMail::class, fn ($mail) => $mail->key === 'kandidat_ditolak');
    }

    public function test_does_not_reject_when_deadline_not_passed(): void
    {
        $vacancy = Vacancy::factory()->published()->create([
            'tenggat_lamaran' => now()->addDays(5)->toDateString(),
        ]);
        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        $stage = ApplicationStage::factory()->reserved()->create(['application_id' => $application->id]);

        $this->artisan('pipeline:auto-reject-reserved')->assertSuccessful();

        $this->assertDatabaseHas('application_stages', [
            'id' => $stage->id,
            'status' => ApplicationStageStatus::Reserved->value,
        ]);
    }

    public function test_does_not_reject_when_deadline_is_today(): void
    {
        $vacancy = Vacancy::factory()->published()->create([
            'tenggat_lamaran' => now()->toDateString(),
        ]);
        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        $stage = ApplicationStage::factory()->reserved()->create(['application_id' => $application->id]);

        $this->artisan('pipeline:auto-reject-reserved')->assertSuccessful();

        $this->assertDatabaseHas('application_stages', [
            'id' => $stage->id,
            'status' => ApplicationStageStatus::Reserved->value,
        ]);
    }

    public function test_does_not_affect_passed_candidates(): void
    {
        $vacancy = Vacancy::factory()->expired()->create();
        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        $stage = ApplicationStage::factory()->create([
            'application_id' => $application->id,
            'status' => ApplicationStageStatus::Selesai,
        ]);

        $this->artisan('pipeline:auto-reject-reserved')->assertSuccessful();

        $this->assertDatabaseHas('application_stages', [
            'id' => $stage->id,
            'status' => ApplicationStageStatus::Selesai->value,
        ]);
    }

    public function test_does_not_affect_already_failed_candidates(): void
    {
        $vacancy = Vacancy::factory()->expired()->create();
        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        $stage = ApplicationStage::factory()->create([
            'application_id' => $application->id,
            'status' => ApplicationStageStatus::Gagal,
        ]);

        $this->artisan('pipeline:auto-reject-reserved')->assertSuccessful();

        $this->assertDatabaseHas('application_stages', [
            'id' => $stage->id,
            'status' => ApplicationStageStatus::Gagal->value,
        ]);
    }

    public function test_does_not_reject_reserved_candidates_of_draft_vacancy(): void
    {
        $vacancy = Vacancy::factory()->create([
            'tenggat_lamaran' => now()->subDay()->toDateString(),
        ]);
        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        $stage = ApplicationStage::factory()->reserved()->create(['application_id' => $application->id]);

        $this->artisan('pipeline:auto-reject-reserved')->assertSuccessful();

        $this->assertDatabaseHas('application_stages', [
            'id' => $stage->id,
            'status' => ApplicationStageStatus::Reserved->value,
        ]);
    }

    public function test_rejects_reserved_candidates_of_closed_vacancy(): void
    {
        $vacancy = Vacancy::factory()->closed()->create([
            'tenggat_lamaran' => now()->subDay()->toDateString(),
        ]);
        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        $stage = ApplicationStage::factory()->reserved()->create(['application_id' => $application->id]);

        $this->artisan('pipeline:auto-reject-reserved')->assertSuccessful();

        $this->assertDatabaseHas('application_stages', [
            'id' => $stage->id,
            'status' => ApplicationStageStatus::Gagal->value,
        ]);
    }

    public function test_handles_multiple_vacancies_with_reserved_candidates(): void
    {
        [$vacancy1, $app1, $stage1] = $this->createExpiredVacancyWithReservedApplication();
        [$vacancy2, $app2, $stage2] = $this->createExpiredVacancyWithReservedApplication();

        $this->artisan('pipeline:auto-reject-reserved')->assertSuccessful();

        $this->assertDatabaseHas('application_stages', [
            'id' => $stage1->id,
            'status' => ApplicationStageStatus::Gagal->value,
        ]);
        $this->assertDatabaseHas('application_stages', [
            'id' => $stage2->id,
            'status' => ApplicationStageStatus::Gagal->value,
        ]);
    }

    public function test_idempotency_running_twice_does_not_double_reject(): void
    {
        $this->createExpiredVacancyWithReservedApplication();

        $this->artisan('pipeline:auto-reject-reserved')->assertSuccessful();
        $this->artisan('pipeline:auto-reject-reserved')->assertSuccessful();

        $this->assertDatabaseCount('application_stages', 1);
        $this->assertDatabaseHas('application_stages', [
            'status' => ApplicationStageStatus::Gagal->value,
        ]);
    }
}
