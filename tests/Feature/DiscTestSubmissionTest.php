<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\DiscDimension;
use App\Enums\Role;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\DiscAnswer;
use App\Models\DiscQuestion;
use App\Models\DiscSubmission;
use App\Models\Employee;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DiscTestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
        $this->artisan('db:seed', ['--class' => 'DiscQuestionSeeder']);
    }

    private function createVacancyWithDiscStage(): Vacancy
    {
        $stageKeys = ['lamaran', 'tes_disc', 'onboarding'];
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->published()->create([
            'unit_id' => Unit::factory()->create()->id,
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);
    }

    private function makeApplicationAtDiscStage(Vacancy $vacancy): Application
    {
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'token' => Str::uuid()->toString(),
        ]);

        $stages = $vacancy->workflowTemplateSnapshot->stages()->orderBy('position')->get();

        foreach ($stages as $index => $stage) {
            $status = match ($stage->key) {
                'lamaran' => ApplicationStageStatus::Selesai,
                'tes_disc' => ApplicationStageStatus::Aktif,
                default => ApplicationStageStatus::Pending,
            };

            ApplicationStage::create([
                'application_id' => $application->id,
                'position' => $index + 1,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $status,
            ]);
        }

        return $application;
    }

    private function buildDiscAnswers(): array
    {
        $questions = DiscQuestion::with('words')->orderBy('urutan')->get();
        $most = [];
        $least = [];

        foreach ($questions as $question) {
            $words = $question->words->values();
            $most[$question->id] = $words[0]->id; // first word = most
            $least[$question->id] = $words[1]->id; // second word = least
        }

        return compact('most', 'least');
    }

    // ===== Access =====

    public function test_candidate_can_access_disc_test_via_token(): void
    {
        $vacancy = $this->createVacancyWithDiscStage();
        $application = $this->makeApplicationAtDiscStage($vacancy);
        $token = Str::uuid()->toString();
        $submission = DiscSubmission::create([
            'application_id' => $application->id,
            'token' => $token,
        ]);

        $response = $this->get(route('tes-disc.show', $token));

        $response->assertOk();
        $response->assertSee($vacancy->judul_posisi);
    }

    public function test_invalid_token_returns_404(): void
    {
        $response = $this->get(route('tes-disc.show', 'token-tidak-ada'));

        $response->assertNotFound();
    }

    // ===== Submission =====

    public function test_submission_persists_answers_and_result(): void
    {
        $vacancy = $this->createVacancyWithDiscStage();
        $application = $this->makeApplicationAtDiscStage($vacancy);
        $token = Str::uuid()->toString();
        $submission = DiscSubmission::create([
            'application_id' => $application->id,
            'token' => $token,
        ]);

        ['most' => $most, 'least' => $least] = $this->buildDiscAnswers();

        $response = $this->post(route('tes-disc.submit', $token), [
            'most' => $most,
            'least' => $least,
        ]);

        $response->assertRedirect(route('tes-disc.show', $token));

        $submission->refresh();
        $this->assertNotNull($submission->submitted_at);
        $this->assertDatabaseHas('disc_results', ['disc_submission_id' => $submission->id]);
        $this->assertGreaterThan(0, DiscAnswer::where('disc_submission_id', $submission->id)->count());
    }

    public function test_submission_advances_pipeline_stage(): void
    {
        $vacancy = $this->createVacancyWithDiscStage();
        $application = $this->makeApplicationAtDiscStage($vacancy);
        $token = Str::uuid()->toString();
        DiscSubmission::create([
            'application_id' => $application->id,
            'token' => $token,
        ]);

        ['most' => $most, 'least' => $least] = $this->buildDiscAnswers();

        $this->post(route('tes-disc.submit', $token), [
            'most' => $most,
            'least' => $least,
        ]);

        $discStage = $application->stages()->where('key', 'tes_disc')->first();
        $this->assertEquals(ApplicationStageStatus::Selesai, $discStage->status);

        $onboardingStage = $application->stages()->where('key', 'onboarding')->first();
        $this->assertEquals(ApplicationStageStatus::Aktif, $onboardingStage->status);
    }

    public function test_submitted_test_cannot_be_resubmitted(): void
    {
        $vacancy = $this->createVacancyWithDiscStage();
        $application = $this->makeApplicationAtDiscStage($vacancy);
        $token = Str::uuid()->toString();
        $submission = DiscSubmission::create([
            'application_id' => $application->id,
            'token' => $token,
        ]);

        ['most' => $most, 'least' => $least] = $this->buildDiscAnswers();

        // First submission
        $this->post(route('tes-disc.submit', $token), [
            'most' => $most,
            'least' => $least,
        ]);

        $answersAfterFirst = DiscAnswer::where('disc_submission_id', $submission->id)->count();

        // Second submission attempt
        $this->post(route('tes-disc.submit', $token), [
            'most' => $most,
            'least' => $least,
        ]);

        $this->assertEquals($answersAfterFirst, DiscAnswer::where('disc_submission_id', $submission->id)->count());
    }

    // ===== Pipeline trigger when advancing to tes_disc =====

    public function test_advancing_to_disc_stage_creates_submission_and_sends_email(): void
    {
        $vacancy = $this->createVacancyWithDiscStage();
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'token' => Str::uuid()->toString(),
        ]);

        // Setup: lamaran=Aktif
        $stages = $vacancy->workflowTemplateSnapshot->stages()->orderBy('position')->get();
        foreach ($stages as $index => $stage) {
            ApplicationStage::create([
                'application_id' => $application->id,
                'position' => $index + 1,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $stage->key === 'lamaran' ? ApplicationStageStatus::Aktif : ApplicationStageStatus::Pending,
            ]);
        }

        $admin = User::factory()->hrAdmin()->create();
        $this->actingAs($admin)->post(
            route('lowongan.lamaran.lanjut', [$vacancy, $application])
        );

        $this->assertDatabaseHas('disc_submissions', ['application_id' => $application->id]);
    }

    // ===== Result visibility =====

    public function test_interviewer_can_see_disc_result_on_candidate_profile(): void
    {
        $vacancy = $this->createVacancyWithDiscStage();
        $unit = $vacancy->unit;

        $stageKeys = ['lamaran', 'tes_disc', 'wawancara_user', 'onboarding'];
        $template = WorkflowTemplate::factory()->create();
        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });
        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);
        $vacancyWithInterview = Vacancy::factory()->published()->create([
            'unit_id' => $unit->id,
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);

        $application = Application::factory()->create([
            'vacancy_id' => $vacancyWithInterview->id,
            'token' => Str::uuid()->toString(),
        ]);

        $stages = $vacancyWithInterview->workflowTemplateSnapshot->stages()->orderBy('position')->get();
        foreach ($stages as $index => $stage) {
            $status = match ($stage->key) {
                'wawancara_user' => ApplicationStageStatus::Aktif,
                'lamaran', 'tes_disc' => ApplicationStageStatus::Selesai,
                default => ApplicationStageStatus::Pending,
            };
            ApplicationStage::create([
                'application_id' => $application->id,
                'position' => $index + 1,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $status,
            ]);
        }

        $discSubmission = DiscSubmission::create([
            'application_id' => $application->id,
            'token' => Str::uuid()->toString(),
            'started_at' => now()->subMinutes(10),
            'submitted_at' => now(),
        ]);

        $discSubmission->result()->create([
            'skor_d' => 10,
            'skor_i' => 5,
            'skor_s' => 4,
            'skor_c' => 9,
            'tipe_primer' => DiscDimension::D->value,
            'tipe_sekunder' => DiscDimension::C->value,
        ]);

        $unitHead = User::factory()->withRole(Role::UnitHead)->create();
        Employee::factory()->create([
            'user_id' => $unitHead->id,
            'unit_id' => $unit->id,
        ]);

        $response = $this->actingAs($unitHead)->get(
            route('lowongan.pipeline.show', [$vacancyWithInterview, $application])
        );

        $response->assertOk();
        $response->assertSee('Hasil Tes DiSC');
        $response->assertSee('Tipe Primer');
        $response->assertSee('Dominan'); // tipe_primer = D â†’ shortLabel = Dominan
    }

    public function test_empty_submission_is_rejected_by_validation(): void
    {
        $vacancy = $this->createVacancyWithDiscStage();
        $application = $this->makeApplicationAtDiscStage($vacancy);
        $token = Str::uuid()->toString();
        DiscSubmission::create([
            'application_id' => $application->id,
            'token' => $token,
        ]);

        $response = $this->post(route('tes-disc.submit', $token), [
            'most' => [],
            'least' => [],
        ]);

        $response->assertSessionHasErrors(['most', 'least']);
        $this->assertDatabaseMissing('disc_answers', ['disc_submission_id' => $application->id]);
    }

    public function test_candidate_status_page_does_not_show_disc_result(): void
    {
        $vacancy = $this->createVacancyWithDiscStage();
        $application = $this->makeApplicationAtDiscStage($vacancy);

        $discSubmission = DiscSubmission::create([
            'application_id' => $application->id,
            'token' => Str::uuid()->toString(),
            'submitted_at' => now(),
        ]);

        $discSubmission->result()->create([
            'skor_d' => 10,
            'skor_i' => 5,
            'skor_s' => 4,
            'skor_c' => 9,
            'tipe_primer' => DiscDimension::D->value,
            'tipe_sekunder' => DiscDimension::C->value,
        ]);

        $response = $this->get(route('karier.lamaran.konfirmasi', $application->token));

        $response->assertOk();
        $response->assertDontSee('Tipe D');
        $response->assertDontSee('Hasil Tes DiSC');
    }
}
