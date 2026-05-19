<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\QuestionType;
use App\Enums\Role;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Question;
use App\Models\QuestionBankTemplate;
use App\Models\QuestionOption;
use App\Models\Stage;
use App\Models\TestSubmission;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyTest;
use App\Models\VacancyTestSnapshot;
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
        $stageKeys = ['lamaran', 'tes_kompetensi', 'onboarding'];
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

        $questionBankTemplate = QuestionBankTemplate::factory()->create();

        $question = Question::factory()->create(['question_bank_template_id' => $questionBankTemplate->id, 'tipe' => QuestionType::Mc->value, 'nilai_poin' => 10, 'urutan' => 1]);
        $correct = QuestionOption::factory()->correct()->create(['question_id' => $question->id]);
        QuestionOption::factory()->create(['question_id' => $question->id]);

        $essayQuestion = Question::factory()->essay()->create(['question_bank_template_id' => $questionBankTemplate->id, 'nilai_poin' => 20, 'urutan' => 2]);

        $vacancyTest = VacancyTest::create(['vacancy_id' => $vacancy->id, 'batas_waktu_menit' => 60]);
        $vacancyTest->questions()->attach($question->id, ['urutan' => 1]);
        $vacancyTest->questions()->attach($essayQuestion->id, ['urutan' => 2]);

        $testSnapshot = VacancyTestSnapshot::createFromVacancyTest($vacancyTest);

        $snapshotMcQuestion = $testSnapshot->questions()->where('tipe', 'mc')->first();
        $snapshotEssayQuestion = $testSnapshot->questions()->where('tipe', 'essay')->first();
        $snapshotCorrectOption = $snapshotMcQuestion->options()->where('is_correct', true)->first();

        return compact('vacancy', 'vacancyTest', 'testSnapshot', 'question', 'correct', 'essayQuestion', 'snapshotMcQuestion', 'snapshotEssayQuestion', 'snapshotCorrectOption');
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
                'lamaran' => ApplicationStageStatus::Selesai,
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

    // ===== Question Bank Template CRUD =====

    public function test_hr_admin_can_view_template_list(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        QuestionBankTemplate::factory(3)->create();

        $response = $this->actingAs($admin)->get(route('template-bank-soal.index'));

        $response->assertOk();
    }

    public function test_non_hr_admin_cannot_view_template_list(): void
    {
        $this->seedStages();
        $user = User::factory()->withRole(Role::Employee)->create();

        $response = $this->actingAs($user)->get(route('template-bank-soal.index'));

        $response->assertForbidden();
    }

    public function test_hr_admin_can_create_template_with_mc_question(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();

        $response = $this->actingAs($admin)->post(route('template-bank-soal.store'), [
            'nama' => 'Tes Kompetensi Perawat',
            'questions' => [
                [
                    'tipe' => 'mc',
                    'pertanyaan' => 'Apa itu hemoglobin?',
                    'nilai_poin' => 5,
                    'options' => [
                        ['teks_opsi' => 'Protein pembawa oksigen'],
                        ['teks_opsi' => 'Jenis sel darah putih'],
                        ['teks_opsi' => 'Enzim pencernaan'],
                    ],
                    'correct_option' => 0,
                ],
            ],
        ]);

        $response->assertRedirect(route('template-bank-soal.index'));
        $this->assertDatabaseHas('question_bank_templates', ['nama' => 'Tes Kompetensi Perawat']);
        $this->assertDatabaseHas('questions', ['pertanyaan' => 'Apa itu hemoglobin?', 'tipe' => 'mc']);
        $this->assertDatabaseHas('question_options', ['teks_opsi' => 'Protein pembawa oksigen', 'is_correct' => true]);
    }

    public function test_hr_admin_can_create_template_with_essay_question(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();

        $response = $this->actingAs($admin)->post(route('template-bank-soal.store'), [
            'nama' => 'Tes Esai Medis',
            'questions' => [
                [
                    'tipe' => 'essay',
                    'pertanyaan' => 'Jelaskan prosedur sterilisasi alat medis.',
                    'nilai_poin' => 20,
                ],
            ],
        ]);

        $response->assertRedirect(route('template-bank-soal.index'));
        $this->assertDatabaseHas('questions', ['tipe' => 'essay', 'nilai_poin' => 20]);
    }

    public function test_hr_admin_can_delete_template_and_questions_cascade(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $template = QuestionBankTemplate::factory()->create();
        $question = Question::factory()->create(['question_bank_template_id' => $template->id, 'urutan' => 1]);

        $response = $this->actingAs($admin)->delete(route('template-bank-soal.destroy', $template));

        $response->assertRedirect(route('template-bank-soal.index'));
        $this->assertDatabaseMissing('question_bank_templates', ['id' => $template->id]);
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
        $this->assertDatabaseHas('vacancy_test_snapshots', ['batas_waktu_menit' => 45]);
    }

    // ===== Candidate Test-Taking =====

    public function test_candidate_can_access_test_via_token(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        ['vacancy' => $vacancy, 'testSnapshot' => $testSnapshot] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->create([
            'application_id' => $application->id,
            'vacancy_test_snapshot_id' => $testSnapshot->id,
        ]);

        $response = $this->get(route('tes.show', $submission->token));

        $response->assertOk();
        $this->assertDatabaseHas('test_submissions', ['id' => $submission->id, 'started_at' => now()->format('Y-m-d H:i:s')]);
    }

    public function test_candidate_cannot_retake_submitted_test(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        ['vacancy' => $vacancy, 'testSnapshot' => $testSnapshot] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->submitted()->create([
            'application_id' => $application->id,
            'vacancy_test_snapshot_id' => $testSnapshot->id,
        ]);

        $response = $this->get(route('tes.show', $submission->token));

        $response->assertOk();
        $response->assertViewIs('test.show');
    }

    public function test_mc_questions_are_auto_scored_on_submission(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        [
            'vacancy' => $vacancy,
            'testSnapshot' => $testSnapshot,
            'snapshotMcQuestion' => $snapshotMcQuestion,
            'snapshotCorrectOption' => $snapshotCorrectOption,
            'snapshotEssayQuestion' => $snapshotEssayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->create([
            'application_id' => $application->id,
            'vacancy_test_snapshot_id' => $testSnapshot->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $response = $this->post(route('tes.submit', $submission->token), [
            'answers' => [
                $snapshotMcQuestion->id => $snapshotCorrectOption->id,
                $snapshotEssayQuestion->id => 'Jawaban esai saya',
            ],
        ]);

        $response->assertRedirect(route('tes.show', $submission->token));
        $submission->refresh();
        $this->assertNotNull($submission->submitted_at);
        $this->assertDatabaseHas('test_answers', [
            'test_submission_id' => $submission->id,
            'vacancy_test_snapshot_question_id' => $snapshotMcQuestion->id,
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
            'testSnapshot' => $testSnapshot,
            'snapshotMcQuestion' => $snapshotMcQuestion,
            'snapshotCorrectOption' => $snapshotCorrectOption,
            'snapshotEssayQuestion' => $snapshotEssayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->create([
            'application_id' => $application->id,
            'vacancy_test_snapshot_id' => $testSnapshot->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $this->post(route('tes.submit', $submission->token), [
            'answers' => [
                $snapshotMcQuestion->id => $snapshotCorrectOption->id,
                $snapshotEssayQuestion->id => 'Jawaban esai saya',
            ],
        ]);

        $this->assertDatabaseHas('test_answers', [
            'test_submission_id' => $submission->id,
            'vacancy_test_snapshot_question_id' => $snapshotEssayQuestion->id,
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
            'testSnapshot' => $testSnapshot,
            'snapshotMcQuestion' => $snapshotMcQuestion,
            'snapshotCorrectOption' => $snapshotCorrectOption,
            'snapshotEssayQuestion' => $snapshotEssayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->create([
            'application_id' => $application->id,
            'vacancy_test_snapshot_id' => $testSnapshot->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $this->post(route('tes.submit', $submission->token), [
            'answers' => [
                $snapshotMcQuestion->id => $snapshotCorrectOption->id,
                $snapshotEssayQuestion->id => 'Jawaban',
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
            'testSnapshot' => $testSnapshot,
            'snapshotMcQuestion' => $snapshotMcQuestion,
            'snapshotCorrectOption' => $snapshotCorrectOption,
            'snapshotEssayQuestion' => $snapshotEssayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->create([
            'application_id' => $application->id,
            'vacancy_test_snapshot_id' => $testSnapshot->id,
            'started_at' => now()->subMinutes(5),
        ]);

        $this->post(route('tes.submit', $submission->token), [
            'answers' => [
                $snapshotMcQuestion->id => $snapshotCorrectOption->id,
                $snapshotEssayQuestion->id => 'Jawaban',
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
            'testSnapshot' => $testSnapshot,
            'snapshotEssayQuestion' => $snapshotEssayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->submitted()->create([
            'application_id' => $application->id,
            'vacancy_test_snapshot_id' => $testSnapshot->id,
        ]);

        $answer = $submission->answers()->create([
            'vacancy_test_snapshot_question_id' => $snapshotEssayQuestion->id,
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
            'testSnapshot' => $testSnapshot,
            'snapshotEssayQuestion' => $snapshotEssayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->submitted()->create([
            'application_id' => $application->id,
            'vacancy_test_snapshot_id' => $testSnapshot->id,
        ]);

        $answer = $submission->answers()->create([
            'vacancy_test_snapshot_question_id' => $snapshotEssayQuestion->id,
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
            'testSnapshot' => $testSnapshot,
            'snapshotMcQuestion' => $snapshotMcQuestion,
            'snapshotEssayQuestion' => $snapshotEssayQuestion,
        ] = $this->createVacancyWithTest($unit);

        $application = $this->makeApplicationAtTestStage($vacancy);
        $submission = TestSubmission::factory()->submitted()->create([
            'application_id' => $application->id,
            'vacancy_test_snapshot_id' => $testSnapshot->id,
            'total_skor' => null,
        ]);

        $submission->answers()->create([
            'vacancy_test_snapshot_question_id' => $snapshotMcQuestion->id,
            'vacancy_test_snapshot_option_id' => null,
            'skor' => 10,
            'is_reviewed' => true,
        ]);

        $answer = $submission->answers()->create([
            'vacancy_test_snapshot_question_id' => $snapshotEssayQuestion->id,
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
