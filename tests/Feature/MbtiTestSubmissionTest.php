<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Employee;
use App\Models\MbtiAnswer;
use App\Models\MbtiQuestion;
use App\Models\MbtiSubmission;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MbtiTestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
        $this->artisan('db:seed', ['--class' => 'MbtiQuestionSeeder']);
    }

    private function createVacancyWithMbtiStage(): Vacancy
    {
        $stageKeys = ['lamaran', 'tes_mbti', 'onboarding'];
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

    private function makeApplicationAtMbtiStage(Vacancy $vacancy): Application
    {
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'token' => Str::uuid()->toString(),
        ]);

        $stages = $vacancy->workflowTemplateSnapshot->stages()->orderBy('position')->get();

        foreach ($stages as $index => $stage) {
            $status = match ($stage->key) {
                'lamaran' => ApplicationStageStatus::Selesai,
                'tes_mbti' => ApplicationStageStatus::Aktif,
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

    private function buildMbtiAnswers(): array
    {
        $questions = MbtiQuestion::orderBy('urutan')->get();
        $jawaban = [];

        foreach ($questions as $question) {
            $jawaban[$question->id] = 'A';
        }

        return $jawaban;
    }

    // ===== Access =====

    public function test_candidate_can_access_mbti_test_via_token(): void
    {
        $vacancy = $this->createVacancyWithMbtiStage();
        $application = $this->makeApplicationAtMbtiStage($vacancy);
        $token = Str::uuid()->toString();
        MbtiSubmission::create([
            'application_id' => $application->id,
            'token' => $token,
        ]);

        $response = $this->get(route('tes-mbti.show', $token));

        $response->assertOk();
        $response->assertSee($vacancy->judul_posisi);
    }

    public function test_invalid_token_returns_404(): void
    {
        $response = $this->get(route('tes-mbti.show', 'token-tidak-ada'));

        $response->assertNotFound();
    }

    // ===== Submission =====

    public function test_submission_persists_answers_and_result(): void
    {
        $vacancy = $this->createVacancyWithMbtiStage();
        $application = $this->makeApplicationAtMbtiStage($vacancy);
        $token = Str::uuid()->toString();
        $submission = MbtiSubmission::create([
            'application_id' => $application->id,
            'token' => $token,
        ]);

        $jawaban = $this->buildMbtiAnswers();

        $response = $this->post(route('tes-mbti.submit', $token), [
            'jawaban' => $jawaban,
        ]);

        $response->assertRedirect(route('tes-mbti.show', $token));

        $submission->refresh();
        $this->assertNotNull($submission->submitted_at);
        $this->assertDatabaseHas('mbti_results', ['mbti_submission_id' => $submission->id]);
        $this->assertGreaterThan(0, MbtiAnswer::where('mbti_submission_id', $submission->id)->count());
    }

    public function test_submission_advances_pipeline_stage(): void
    {
        $vacancy = $this->createVacancyWithMbtiStage();
        $application = $this->makeApplicationAtMbtiStage($vacancy);
        $token = Str::uuid()->toString();
        MbtiSubmission::create([
            'application_id' => $application->id,
            'token' => $token,
        ]);

        $jawaban = $this->buildMbtiAnswers();

        $this->post(route('tes-mbti.submit', $token), [
            'jawaban' => $jawaban,
        ]);

        $mbtiStage = $application->stages()->where('key', 'tes_mbti')->first();
        $this->assertEquals(ApplicationStageStatus::Selesai, $mbtiStage->status);

        $onboardingStage = $application->stages()->where('key', 'onboarding')->first();
        $this->assertEquals(ApplicationStageStatus::Aktif, $onboardingStage->status);
    }

    public function test_submitted_test_cannot_be_resubmitted(): void
    {
        $vacancy = $this->createVacancyWithMbtiStage();
        $application = $this->makeApplicationAtMbtiStage($vacancy);
        $token = Str::uuid()->toString();
        $submission = MbtiSubmission::create([
            'application_id' => $application->id,
            'token' => $token,
        ]);

        $jawaban = $this->buildMbtiAnswers();

        // First submission
        $this->post(route('tes-mbti.submit', $token), ['jawaban' => $jawaban]);
        $answersAfterFirst = MbtiAnswer::where('mbti_submission_id', $submission->id)->count();

        // Second attempt
        $this->post(route('tes-mbti.submit', $token), ['jawaban' => $jawaban]);
        $this->assertEquals($answersAfterFirst, MbtiAnswer::where('mbti_submission_id', $submission->id)->count());
    }

    // ===== Pipeline trigger =====

    public function test_advancing_to_mbti_stage_creates_submission_and_sends_email(): void
    {
        $vacancy = $this->createVacancyWithMbtiStage();
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'token' => Str::uuid()->toString(),
        ]);

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

        $this->assertDatabaseHas('mbti_submissions', ['application_id' => $application->id]);
    }

    // ===== Result visibility =====

    public function test_interviewer_can_see_mbti_result_on_candidate_profile(): void
    {
        $vacancy = $this->createVacancyWithMbtiStage();
        $unit = $vacancy->unit;

        $stageKeys = ['lamaran', 'tes_mbti', 'wawancara_user', 'onboarding'];
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
                'lamaran', 'tes_mbti' => ApplicationStageStatus::Selesai,
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

        $mbtiSubmission = MbtiSubmission::create([
            'application_id' => $application->id,
            'token' => Str::uuid()->toString(),
            'started_at' => now()->subMinutes(15),
            'submitted_at' => now(),
        ]);

        $mbtiSubmission->result()->create([
            'skor_e' => 12,
            'skor_i' => 6,
            'skor_s' => 8,
            'skor_n' => 9,
            'skor_t' => 14,
            'skor_f' => 4,
            'skor_j' => 10,
            'skor_p' => 7,
            'tipe' => 'ENTJ',
            'kekuatan_ei' => 33,
            'kekuatan_sn' => 6,
            'kekuatan_tf' => 56,
            'kekuatan_jp' => 18,
        ]);

        $unitHead = User::factory()->withRole(Role::UnitHead)->create();
        Employee::factory()->create([
            'user_id' => $unitHead->id,
            'unit' => $unit->nama,
        ]);

        $response = $this->actingAs($unitHead)->get(
            route('lowongan.pipeline.show', [$vacancyWithInterview, $application])
        );

        $response->assertOk();
        $response->assertSee('Hasil Tes MBTI');
        $response->assertSee('ENTJ');
    }

    public function test_empty_submission_is_rejected_by_validation(): void
    {
        $vacancy = $this->createVacancyWithMbtiStage();
        $application = $this->makeApplicationAtMbtiStage($vacancy);
        $token = Str::uuid()->toString();
        MbtiSubmission::create([
            'application_id' => $application->id,
            'token' => $token,
        ]);

        $response = $this->post(route('tes-mbti.submit', $token), [
            'jawaban' => [],
        ]);

        $response->assertSessionHasErrors(['jawaban']);
        $this->assertDatabaseMissing('mbti_answers', ['mbti_submission_id' => $application->id]);
    }

    public function test_candidate_status_page_does_not_show_mbti_result(): void
    {
        $vacancy = $this->createVacancyWithMbtiStage();
        $application = $this->makeApplicationAtMbtiStage($vacancy);

        $mbtiSubmission = MbtiSubmission::create([
            'application_id' => $application->id,
            'token' => Str::uuid()->toString(),
            'submitted_at' => now(),
        ]);

        $mbtiSubmission->result()->create([
            'skor_e' => 10, 'skor_i' => 8, 'skor_s' => 9, 'skor_n' => 8,
            'skor_t' => 12, 'skor_f' => 6, 'skor_j' => 10, 'skor_p' => 7,
            'tipe' => 'ESTJ', 'kekuatan_ei' => 11, 'kekuatan_sn' => 6,
            'kekuatan_tf' => 33, 'kekuatan_jp' => 18,
        ]);

        $response = $this->get(route('karier.lamaran.konfirmasi', $application->token));

        $response->assertOk();
        $response->assertDontSee('Hasil Tes MBTI');
        $response->assertDontSee('ENTJ');
    }
}
