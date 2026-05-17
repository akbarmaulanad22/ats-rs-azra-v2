<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\Stage;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateStatusPageTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
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

    public function test_status_page_renders_for_valid_token(): void
    {
        $this->seedStages();
        $vacancy = $this->createVacancyWithStages();
        $application = $this->makeApplication($vacancy);

        $response = $this->get(route('karier.lamaran.status', $application->token));

        $response->assertOk();
        $response->assertSee($application->candidate->nama_lengkap);
        $response->assertSee($application->vacancy->judul_posisi);
    }

    public function test_status_page_returns_404_for_invalid_token(): void
    {
        $response = $this->get(route('karier.lamaran.status', 'invalid-token-that-does-not-exist'));

        $response->assertNotFound();
    }

    public function test_status_page_shows_all_stages(): void
    {
        $this->seedStages();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $application = $this->makeApplication($vacancy);
        $application->load('stages');

        $response = $this->get(route('karier.lamaran.status', $application->token));

        $response->assertOk();
        foreach ($application->stages as $stage) {
            $response->assertSee($stage->nama);
        }
    }

    public function test_completed_stage_shows_selesai_with_date(): void
    {
        $this->seedStages();
        $vacancy = $this->createVacancyWithStages();
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Aktif,
            2 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->get(route('karier.lamaran.status', $application->token));

        $response->assertOk();
        $response->assertSee('Selesai');
    }

    public function test_active_stage_shows_sedang_berlangsung(): void
    {
        $this->seedStages();
        $vacancy = $this->createVacancyWithStages();
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Aktif,
            2 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->get(route('karier.lamaran.status', $application->token));

        $response->assertOk();
        $response->assertSee('Sedang berlangsung');
    }

    public function test_pending_stage_shows_menunggu(): void
    {
        $this->seedStages();
        $vacancy = $this->createVacancyWithStages();
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Aktif,
            2 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->get(route('karier.lamaran.status', $application->token));

        $response->assertOk();
        $response->assertSee('Menunggu');
    }

    public function test_rejected_candidate_sees_tidak_lolos_at_failed_stage(): void
    {
        $this->seedStages();
        $vacancy = $this->createVacancyWithStages();
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Gagal,
            2 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->get(route('karier.lamaran.status', $application->token));

        $response->assertOk();
        $response->assertSee('Tidak Lolos');
    }

    public function test_reserved_stage_shows_ditangguhkan(): void
    {
        $this->seedStages();
        $vacancy = $this->createVacancyWithStages();
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Reserved,
            2 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->get(route('karier.lamaran.status', $application->token));

        $response->assertOk();
        $response->assertSee('Ditangguhkan');
    }

    public function test_no_sensitive_data_exposed(): void
    {
        $this->seedStages();
        $vacancy = $this->createVacancyWithStages();

        $catatanValue = 'CATATAN_RAHASIA_INTERNAL_12345';
        $application = $this->makeApplication($vacancy);
        $application->load('stages');
        $application->stages->where('status', ApplicationStageStatus::Selesai)->first()
            ->update(['catatan' => $catatanValue]);

        $response = $this->get(route('karier.lamaran.status', $application->token));

        $response->assertOk();
        $response->assertDontSee($catatanValue);
        $response->assertDontSee('catatan');
    }

    public function test_multiple_applications_per_candidate_each_resolve_independently(): void
    {
        $this->seedStages();

        $vacancy1 = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);
        $vacancy2 = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $candidate = Candidate::factory()->create();

        $application1 = Application::factory()->create([
            'vacancy_id' => $vacancy1->id,
            'candidate_id' => $candidate->id,
        ]);

        $application2 = Application::factory()->create([
            'vacancy_id' => $vacancy2->id,
            'candidate_id' => $candidate->id,
        ]);

        $vacancy1->load('workflowTemplateSnapshot.stages');
        $stages1 = $vacancy1->workflowTemplateSnapshot->stages->sortBy('position')->values();
        foreach ($stages1 as $index => $stage) {
            ApplicationStage::factory()->create([
                'application_id' => $application1->id,
                'position' => $stage->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $index === 0 ? ApplicationStageStatus::Selesai : ($index === 1 ? ApplicationStageStatus::Aktif : ApplicationStageStatus::Pending),
            ]);
        }

        $vacancy2->load('workflowTemplateSnapshot.stages');
        $stages2 = $vacancy2->workflowTemplateSnapshot->stages->sortBy('position')->values();
        foreach ($stages2 as $index => $stage) {
            ApplicationStage::factory()->create([
                'application_id' => $application2->id,
                'position' => $stage->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $index === 1 ? ApplicationStageStatus::Gagal : ($index === 0 ? ApplicationStageStatus::Selesai : ApplicationStageStatus::Pending),
            ]);
        }

        $response1 = $this->get(route('karier.lamaran.status', $application1->token));
        $response1->assertOk();
        $response1->assertSee($vacancy1->judul_posisi);
        $response1->assertSee('Sedang berlangsung');
        $response1->assertDontSee('Tidak Lolos');

        $response2 = $this->get(route('karier.lamaran.status', $application2->token));
        $response2->assertOk();
        $response2->assertSee($vacancy2->judul_posisi);
        $response2->assertSee('Tidak Lolos');
    }

    public function test_status_page_accessible_without_authentication(): void
    {
        $this->seedStages();
        $vacancy = $this->createVacancyWithStages();
        $application = $this->makeApplication($vacancy);

        $this->assertGuest();
        $response = $this->get(route('karier.lamaran.status', $application->token));

        $response->assertOk();
    }

    public function test_ui_text_is_in_bahasa_indonesia(): void
    {
        $this->seedStages();
        $vacancy = $this->createVacancyWithStages();
        $application = $this->makeApplication($vacancy);

        $response = $this->get(route('karier.lamaran.status', $application->token));

        $response->assertOk();
        $response->assertSee('Status Lamaran');
        $response->assertSee('Tahapan Seleksi');
        $response->assertSee('Informasi Pelamar');
    }
}
