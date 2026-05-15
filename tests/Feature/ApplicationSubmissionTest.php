<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Enums\VacancyStatus;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Stage;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    private function createPublishedVacancyWithStages(array $stageKeys = ['aplikasi', 'skrining_cv_hr', 'onboarding']): Vacancy
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

    private function validPayload(): array
    {
        Storage::fake('local');

        return [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ];
    }

    // ── Form page ─────────────────────────────────────────────────────────────

    public function test_application_form_accessible_for_published_vacancy(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->get(route('karier.lamar', $vacancy));

        $response->assertStatus(200);
        $response->assertViewIs('career.apply');
    }

    public function test_application_form_returns_404_for_draft_vacancy(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->create(['status' => VacancyStatus::Draft]);

        $response = $this->get(route('karier.lamar', $vacancy));

        $response->assertStatus(404);
    }

    public function test_application_form_returns_404_for_expired_vacancy(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();
        $vacancy->update(['tenggat_lamaran' => now()->subDay()->format('Y-m-d')]);

        $response = $this->get(route('karier.lamar', $vacancy));

        $response->assertStatus(404);
    }

    // ── Happy path ────────────────────────────────────────────────────────────

    public function test_candidate_can_submit_application(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ];

        $response = $this->post(route('karier.lamar.store', $vacancy), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('candidates', [
            'email' => 'budi@example.com',
            'nama_lengkap' => 'Budi Santoso',
        ]);
        $this->assertDatabaseCount('applications', 1);
    }

    public function test_application_stores_cv_file_on_local_disk(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $application = Application::first();
        Storage::disk('local')->assertExists($application->cv_path);
    }

    public function test_application_generates_unique_token(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $application = Application::first();
        $this->assertNotNull($application->token);
        $this->assertNotEmpty($application->token);
    }

    public function test_successful_submission_redirects_to_confirmation_page(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $application = Application::first();
        $response->assertRedirect(route('karier.lamaran.konfirmasi', ['token' => $application->token]));
    }

    // ── Pipeline initialization ───────────────────────────────────────────────

    public function test_pipeline_initialized_from_workflow_snapshot(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $application = Application::first();
        $this->assertCount(3, $application->stages);
    }

    public function test_first_stage_is_selesai_on_submission(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $application = Application::first();
        $firstStage = $application->stages->firstWhere('key', 'aplikasi');

        $this->assertEquals(ApplicationStageStatus::Selesai, $firstStage->status);
    }

    public function test_second_stage_is_aktif_on_submission(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $application = Application::first();
        $secondStage = $application->stages->firstWhere('key', 'skrining_cv_hr');

        $this->assertEquals(ApplicationStageStatus::Aktif, $secondStage->status);
    }

    public function test_remaining_stages_are_pending_on_submission(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $application = Application::first();
        $lastStage = $application->stages->firstWhere('key', 'onboarding');

        $this->assertEquals(ApplicationStageStatus::Pending, $lastStage->status);
    }

    public function test_stages_preserve_position_ordering(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $application = Application::first();
        $positions = $application->stages->pluck('position')->all();

        $this->assertEquals([1, 2, 3], $positions);
    }

    // ── Candidate deduplication ───────────────────────────────────────────────

    public function test_existing_candidate_matched_by_email(): void
    {
        $this->seedStages();
        Storage::fake('local');

        $existingCandidate = Candidate::factory()->create(['email' => 'budi@example.com']);

        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Lain',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $this->assertDatabaseCount('candidates', 1);
        $this->assertEquals($existingCandidate->id, Application::first()->candidate_id);
    }

    // ── Multi-vacancy application ─────────────────────────────────────────────

    public function test_candidate_can_apply_to_multiple_vacancies(): void
    {
        $this->seedStages();
        Storage::fake('local');

        $vacancy1 = $this->createPublishedVacancyWithStages();
        $vacancy2 = $this->createPublishedVacancyWithStages();

        $payload = [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ];

        $this->post(route('karier.lamar.store', $vacancy1), $payload);

        $payload['cv'] = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');
        $this->post(route('karier.lamar.store', $vacancy2), $payload);

        $this->assertDatabaseCount('candidates', 1);
        $this->assertDatabaseCount('applications', 2);
    }

    public function test_duplicate_application_to_same_vacancy_is_rejected(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages();

        $payload = [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ];

        $this->post(route('karier.lamar.store', $vacancy), $payload);

        $payload['cv'] = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');
        $response = $this->post(route('karier.lamar.store', $vacancy), $payload);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('applications', 1);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_cv_must_be_pdf(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ]);

        $response->assertSessionHasErrors('cv');
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_cv_must_not_exceed_5mb(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 6000, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('cv');
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->seedStages();
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->post(route('karier.lamar.store', $vacancy), []);

        $response->assertSessionHasErrors(['nama_lengkap', 'email', 'no_telepon', 'cv']);
    }

    public function test_post_to_draft_vacancy_returns_404(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = Vacancy::factory()->create(['status' => VacancyStatus::Draft]);

        $response = $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $response->assertStatus(404);
    }

    public function test_post_to_expired_vacancy_returns_404(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages();
        $vacancy->update(['tenggat_lamaran' => now()->subDay()->format('Y-m-d')]);

        $response = $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $response->assertStatus(404);
    }

    // ── Confirmation page ─────────────────────────────────────────────────────

    public function test_confirmation_page_accessible_by_token(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $vacancy = $this->createPublishedVacancyWithStages();

        $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $application = Application::first();

        $response = $this->get(route('karier.lamaran.konfirmasi', ['token' => $application->token]));

        $response->assertStatus(200);
        $response->assertViewIs('career.confirmation');
        $response->assertSee('Budi Santoso');
    }

    public function test_confirmation_page_returns_404_for_invalid_token(): void
    {
        $response = $this->get(route('karier.lamaran.konfirmasi', ['token' => 'invalid-token-xyz']));

        $response->assertStatus(404);
    }

    // ── HR Admin pipeline view ────────────────────────────────────────────────

    public function test_hr_admin_can_view_pipeline(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->actingAs($admin)->get(route('lowongan.pipeline', $vacancy));

        $response->assertStatus(200);
        $response->assertViewIs('vacancies.pipeline');
    }

    public function test_pipeline_view_shows_candidates_grouped_by_stage(): void
    {
        $this->seedStages();
        Storage::fake('local');
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createPublishedVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $this->post(route('karier.lamar.store', $vacancy), [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_telepon' => '081234567890',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $response = $this->actingAs($admin)->get(route('lowongan.pipeline', $vacancy));

        $response->assertSee('Budi Santoso');
        $response->assertSee('Skrining CV HR');
    }

    public function test_non_hr_admin_cannot_view_pipeline(): void
    {
        $this->seedStages();
        $user = User::factory()->create(['role' => Role::Employee]);
        $vacancy = $this->createPublishedVacancyWithStages();

        $response = $this->actingAs($user)->get(route('lowongan.pipeline', $vacancy));

        $response->assertStatus(403);
    }
}
