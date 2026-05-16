<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\QuestionType;
use App\Enums\Role;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Stage;
use App\Models\TestSubmission;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyTest;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompetencyTestEngineTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    private function hrAdmin(): User
    {
        return User::factory()->hrAdmin()->create();
    }

    private function createVacancyWithTest(Unit $unit): array
    {
        $stageKeys = ['aplikasi', 'tes_kompetensi', 'onboarding'];
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $vacancy = Vacancy::factory()->published()->create([
            'unit_id' => $unit->id,
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);

        $question = Question::factory()->create(['unit_id' => $unit->id, 'tipe' => QuestionType::Mc->value, 'nilai_poin' => 10]);
        $correct = QuestionOption::factory()->correct()->create(['question_id' => $question->id]);
        QuestionOption::factory()->create(['question_id' => $question->id]);

        $essayQuestion = Question::factory()->essay()->create(['unit_id' => $unit->id, 'nilai_poin' => 20]);

        $vacancyTest = VacancyTest::create(['vacancy_id' => $vacancy->id, 'batas_waktu_menit' => 60]);
        $vacancyTest->questions()->attach($question->id, ['urutan' => 1]);
        $vacancyTest->questions()->attach($essayQuestion->id, ['urutan' => 2]);

        return compact('vacancy', 'vacancyTest', 'question', 'correct', 'essayQuestion');
    }

    private function makeApplicationAtTestStage(Vacancy $vacancy): Application
    {
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'token' => Str::uuid()->toString(),
        ]);

        $stages = $vacancy->workflowTemplateSnapshot->stages()->orderBy('position')->get();
        foreach ($stages as $index => $stage) {
            $status = match ($stage->key) {
                'aplikasi' => ApplicationStageStatus::Selesai,
                'tes_kompetensi' => ApplicationStageStatus::Aktif,
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

    // ===== Question Bank CRUD =====

    public function test_hr_admin_can_view_question_bank(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        Question::factory(3)->create(['unit_id' => Unit::factory()->create()->id]);

        $response = $this->actingAs($admin)->get(route('bank-soal.index'));

        $response->assertOk();
    }

    public function test_non_hr_admin_cannot_view_question_bank(): void
    {
        $this->seedStages();
        $user = User::factory()->withRole(Role::Employee)->create();

        $response = $this->actingAs($user)->get(route('bank-soal.index'));

        $response->assertForbidden();
    }

    public function test_hr_admin_can_create_mc_question(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $unit = Unit::factory()->create();

        $response = $this->actingAs($admin)->post(route('bank-soal.store'), [
            'unit_id' => $unit->id,
            'tipe' => 'mc',
            'pertanyaan' => 'Apa itu hemoglobin?',
            'nilai_poin' => 5,
            'options' => [
                ['teks_opsi' => 'Protein pembawa oksigen'],
                ['teks_opsi' => 'Jenis sel darah putih'],
                ['teks_opsi' => 'Enzim pencernaan'],
            ],
            'correct_option' => 0,
        ]);

        $response->assertRedirect(route('bank-soal.index'));
        $this->assertDatabaseHas('questions', ['pertanyaan' => 'Apa itu hemoglobin?', 'tipe' => 'mc']);
        $this->assertDatabaseHas('question_options', ['teks_opsi' => 'Protein pembawa oksigen', 'is_correct' => true]);
    }

    public function test_hr_admin_can_create_essay_question(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $unit = Unit::factory()->create();

        $response = $this->actingAs($admin)->post(route('bank-soal.store'), [
            'unit_id' => $unit->id,
            'tipe' => 'essay',
            'pertanyaan' => 'Jelaskan prosedur sterilisasi alat medis.',
            'nilai_poin' => 20,
        ]);

        $response->assertRedirect(route('bank-soal.index'));
        $this->assertDatabaseHas('questions', ['tipe' => 'essay', 'nilai_poin' => 20]);
    }

    public function test_hr_admin_can_delete_question(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $unit = Unit::factory()->create();
        $question = Question::factory()->create(['unit_id' => $unit->id]);

        $response = $this->actingAs($admin)->delete(route('bank-soal.destroy', $question));

        $response->assertRedirect(route('bank-soal.index'));
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    // ===== Vacancy Test Configuration =====

    public function test_hr_admin_can_configure_vacancy_test(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $unit = Unit::factory()->create();
        ['vacancy' => $vacancy, 'question' => $question] = $this->createVacancyWithTest($unit);

        $vacancy->vacancyTest()->delete();

        $response = $this->actingAs($admin)->post(route('lowongan.tes.save', $vacancy), [
            'batas_waktu_menit' => 45,
            'question_ids' => [$question->id],
        ]);

        $response->assertRedirect(route('lowongan.tes.show', $vacancy));
        $this->assertDatabaseHas('vacancy_tests', ['vacancy_id' => $vacancy->id, 'batas_waktu_menit' => 45]);
        $this->assertDatabaseHas('vacancy_test_questions', ['question_id' => $question->id]);
    }

    // ===== Candidate Test-Taking =====

    public function test_candidate_can_access_test_via_token(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        ['vacancy' => $vacancy, 'vacancyTest' => $vacancyTest] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->create([
            'application_id' => $application->id,
            'vacancy_test_id' => $vacancyTest->id,
        ]);

        $response = $this->get(route('tes.show', $submission->token));

        $response->assertOk();
        $this->assertDatabaseHas('test_submissions', ['id' => $submission->id, 'started_at' => now()->format('Y-m-d H:i:s')]);
    }

    public function test_candidate_cannot_retake_submitted_test(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        ['vacancy' => $vacancy, 'vacancyTest' => $vacancyTest] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->submitted()->create([
            'application_id' => $application->id,
            'vacancy_test_id' => $vacancyTest->id,
        ]);

        $response = $this->get(route('tes.show', $submission->token));

        $response->assertOk();
        $response->assertViewIs('test.selesai');
    }

    public function test_mc_questions_are_auto_scored_on_submission(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        [
            'vacancy' => $vacancy,
            'vacancyTest' => $vacancyTest,
            'question' => $mcQuestion,
            'correct' => $correct,
            'essayQuestion' => $essayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->create([
            'application_id' => $application->id,
            'vacancy_test_id' => $vacancyTest->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $response = $this->post(route('tes.submit', $submission->token), [
            'answers' => [
                $mcQuestion->id => $correct->id,
                $essayQuestion->id => 'Jawaban esai saya',
            ],
        ]);

        $response->assertRedirect(route('tes.show', $submission->token));
        $submission->refresh();
        $this->assertNotNull($submission->submitted_at);
        $this->assertDatabaseHas('test_answers', [
            'test_submission_id' => $submission->id,
            'question_id' => $mcQuestion->id,
            'skor' => 10,
            'is_reviewed' => true,
        ]);
    }

    public function test_essay_questions_are_flagged_for_manual_review(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        [
            'vacancy' => $vacancy,
            'vacancyTest' => $vacancyTest,
            'question' => $mcQuestion,
            'correct' => $correct,
            'essayQuestion' => $essayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->create([
            'application_id' => $application->id,
            'vacancy_test_id' => $vacancyTest->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $this->post(route('tes.submit', $submission->token), [
            'answers' => [
                $mcQuestion->id => $correct->id,
                $essayQuestion->id => 'Jawaban esai saya',
            ],
        ]);

        $this->assertDatabaseHas('test_answers', [
            'test_submission_id' => $submission->id,
            'question_id' => $essayQuestion->id,
            'is_reviewed' => false,
            'skor' => null,
        ]);
    }

    public function test_pipeline_does_not_auto_advance_after_test_submission(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        [
            'vacancy' => $vacancy,
            'vacancyTest' => $vacancyTest,
            'question' => $mcQuestion,
            'correct' => $correct,
            'essayQuestion' => $essayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->create([
            'application_id' => $application->id,
            'vacancy_test_id' => $vacancyTest->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $this->post(route('tes.submit', $submission->token), [
            'answers' => [
                $mcQuestion->id => $correct->id,
                $essayQuestion->id => 'Jawaban',
            ],
        ]);

        $testStage = ApplicationStage::where('application_id', $application->id)
            ->where('key', 'tes_kompetensi')
            ->first();

        $this->assertEquals(ApplicationStageStatus::Aktif, $testStage->status);
    }

    public function test_double_submit_is_idempotent(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        [
            'vacancy' => $vacancy,
            'vacancyTest' => $vacancyTest,
            'question' => $mcQuestion,
            'correct' => $correct,
            'essayQuestion' => $essayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->create([
            'application_id' => $application->id,
            'vacancy_test_id' => $vacancyTest->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $this->post(route('tes.submit', $submission->token), [
            'answers' => [
                $mcQuestion->id => $correct->id,
                $essayQuestion->id => 'Jawaban',
            ],
        ]);

        $this->post(route('tes.submit', $submission->token), [
            'answers' => [],
        ]);

        $this->assertDatabaseCount('test_answers', 2);
    }

    // ===== Essay Scoring =====

    public function test_hr_admin_can_score_essay_answer(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $unit = Unit::factory()->create();
        [
            'vacancy' => $vacancy,
            'vacancyTest' => $vacancyTest,
            'essayQuestion' => $essayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->submitted()->create([
            'application_id' => $application->id,
            'vacancy_test_id' => $vacancyTest->id,
        ]);

        $answer = $submission->answers()->create([
            'question_id' => $essayQuestion->id,
            'jawaban_teks' => 'Jawaban esai',
            'skor' => null,
            'is_reviewed' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('lowongan.tes.ulasan.skor', [$vacancy, $answer]), [
            'skor' => 15,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('test_answers', ['id' => $answer->id, 'skor' => 15, 'is_reviewed' => true]);
    }

    public function test_essay_score_cannot_exceed_question_point_value(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $unit = Unit::factory()->create();
        [
            'vacancy' => $vacancy,
            'vacancyTest' => $vacancyTest,
            'essayQuestion' => $essayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->submitted()->create([
            'application_id' => $application->id,
            'vacancy_test_id' => $vacancyTest->id,
        ]);

        $answer = $submission->answers()->create([
            'question_id' => $essayQuestion->id,
            'jawaban_teks' => 'Jawaban esai',
            'skor' => null,
            'is_reviewed' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('lowongan.tes.ulasan.skor', [$vacancy, $answer]), [
            'skor' => 999,
        ]);

        $response->assertSessionHasErrors('skor');
    }

    public function test_total_score_updated_when_all_essays_reviewed(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $unit = Unit::factory()->create();
        [
            'vacancy' => $vacancy,
            'vacancyTest' => $vacancyTest,
            'question' => $mcQuestion,
            'essayQuestion' => $essayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->submitted()->create([
            'application_id' => $application->id,
            'vacancy_test_id' => $vacancyTest->id,
            'total_skor' => 10,
        ]);

        $submission->answers()->create([
            'question_id' => $mcQuestion->id,
            'question_option_id' => null,
            'skor' => 10,
            'is_reviewed' => true,
        ]);

        $answer = $submission->answers()->create([
            'question_id' => $essayQuestion->id,
            'jawaban_teks' => 'Jawaban esai',
            'skor' => null,
            'is_reviewed' => false,
        ]);

        $this->actingAs($admin)->post(route('lowongan.tes.ulasan.skor', [$vacancy, $answer]), [
            'skor' => 18,
        ]);

        $submission->refresh();
        $this->assertEquals(28, $submission->total_skor);
    }
}
