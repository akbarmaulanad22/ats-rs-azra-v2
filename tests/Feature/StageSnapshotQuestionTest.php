<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Stage;
use App\Models\StageSnapshotOption;
use App\Models\StageSnapshotQuestion;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use App\Models\WorkflowTemplateSnapshotStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageSnapshotQuestionTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    private function createBankWithQuestions(): QuestionBank
    {
        $bank = QuestionBank::factory()->create();

        $q1 = Question::factory()->create(['tipe' => QuestionType::Mc->value, 'nilai_poin' => 5]);
        QuestionOption::factory()->correct()->create(['question_id' => $q1->id, 'teks_opsi' => 'Benar']);
        QuestionOption::factory()->create(['question_id' => $q1->id, 'teks_opsi' => 'Salah']);

        $q2 = Question::factory()->essay()->create(['nilai_poin' => 10]);

        $bank->questions()->attach($q1->id, ['urutan' => 1]);
        $bank->questions()->attach($q2->id, ['urutan' => 2]);

        return $bank;
    }

    private function createTemplateWithBank(QuestionBank $bank): WorkflowTemplate
    {
        $this->seedStages();
        $template = WorkflowTemplate::factory()->create();

        $tesKompetensi = Stage::where('key', 'tes_kompetensi')->first();
        $lamaran = Stage::where('key', 'lamaran')->first();
        $onboarding = Stage::where('key', 'onboarding')->first();

        $template->stages()->attach($lamaran->id, ['position' => 1]);
        $template->stages()->attach($tesKompetensi->id, [
            'position' => 2,
            'question_bank_id' => $bank->id,
            'batas_waktu_menit' => 30,
        ]);
        $template->stages()->attach($onboarding->id, ['position' => 3]);

        return $template->load('stages');
    }

    // ── Model & relationships ────────────────────────────────────────────

    public function test_stage_snapshot_question_belongs_to_stage(): void
    {
        $stage = WorkflowTemplateSnapshotStage::factory()->create();
        $question = StageSnapshotQuestion::factory()->create([
            'workflow_template_snapshot_stage_id' => $stage->id,
        ]);

        $this->assertTrue($question->stage->is($stage));
    }

    public function test_stage_snapshot_option_belongs_to_question(): void
    {
        $question = StageSnapshotQuestion::factory()->create();
        $option = StageSnapshotOption::factory()->create([
            'stage_snapshot_question_id' => $question->id,
        ]);

        $this->assertTrue($option->question->is($question));
    }

    public function test_snapshot_stage_has_many_questions(): void
    {
        $stage = WorkflowTemplateSnapshotStage::factory()->create();
        StageSnapshotQuestion::factory()->count(3)->create([
            'workflow_template_snapshot_stage_id' => $stage->id,
        ]);

        $this->assertCount(3, $stage->questions);
    }

    public function test_snapshot_question_has_many_options(): void
    {
        $question = StageSnapshotQuestion::factory()->create();
        StageSnapshotOption::factory()->count(4)->create([
            'stage_snapshot_question_id' => $question->id,
        ]);

        $this->assertCount(4, $question->options);
    }

    // ── correctOption helper ─────────────────────────────────────────────

    public function test_correct_option_returns_correct_one(): void
    {
        $question = StageSnapshotQuestion::factory()->create();
        StageSnapshotOption::factory()->create(['stage_snapshot_question_id' => $question->id]);
        $correct = StageSnapshotOption::factory()->correct()->create(['stage_snapshot_question_id' => $question->id]);

        $this->assertTrue($question->correctOption()->is($correct));
    }

    public function test_correct_option_returns_null_when_none(): void
    {
        $question = StageSnapshotQuestion::factory()->create();
        StageSnapshotOption::factory()->create(['stage_snapshot_question_id' => $question->id]);

        $this->assertNull($question->correctOption());
    }

    // ── totalNilaiMaksimal ───────────────────────────────────────────────

    public function test_total_nilai_maksimal_sums_question_points(): void
    {
        $stage = WorkflowTemplateSnapshotStage::factory()->create();
        StageSnapshotQuestion::factory()->create([
            'workflow_template_snapshot_stage_id' => $stage->id,
            'nilai_poin' => 5,
        ]);
        StageSnapshotQuestion::factory()->create([
            'workflow_template_snapshot_stage_id' => $stage->id,
            'nilai_poin' => 10,
        ]);

        $this->assertEquals(15, $stage->totalNilaiMaksimal());
    }

    public function test_total_nilai_maksimal_zero_when_no_questions(): void
    {
        $stage = WorkflowTemplateSnapshotStage::factory()->create();

        $this->assertEquals(0, $stage->totalNilaiMaksimal());
    }

    // ── createFromTemplate snapshots questions ───────────────────────────

    public function test_snapshot_captures_question_bank_questions(): void
    {
        $bank = $this->createBankWithQuestions();
        $template = $this->createTemplateWithBank($bank);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $tesStage = $snapshot->stages->firstWhere('key', 'tes_kompetensi');
        $this->assertNotNull($tesStage);
        $this->assertCount(2, $tesStage->questions);
    }

    public function test_snapshot_captures_question_data(): void
    {
        $bank = $this->createBankWithQuestions();
        $template = $this->createTemplateWithBank($bank);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $tesStage = $snapshot->stages->firstWhere('key', 'tes_kompetensi');
        $first = $tesStage->questions->firstWhere('urutan', 1);

        $this->assertEquals(QuestionType::Mc, $first->tipe);
        $this->assertEquals(5, $first->nilai_poin);
        $this->assertNotEmpty($first->pertanyaan);
    }

    public function test_snapshot_captures_options(): void
    {
        $bank = $this->createBankWithQuestions();
        $template = $this->createTemplateWithBank($bank);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $tesStage = $snapshot->stages->firstWhere('key', 'tes_kompetensi');
        $mcQuestion = $tesStage->questions->firstWhere('urutan', 1);

        $this->assertCount(2, $mcQuestion->options);
        $this->assertNotNull($mcQuestion->correctOption());
        $this->assertEquals('Benar', $mcQuestion->correctOption()->teks_opsi);
    }

    public function test_snapshot_copies_batas_waktu_menit(): void
    {
        $bank = $this->createBankWithQuestions();
        $template = $this->createTemplateWithBank($bank);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $tesStage = $snapshot->stages->firstWhere('key', 'tes_kompetensi');
        $this->assertEquals(30, $tesStage->batas_waktu_menit);
    }

    public function test_snapshot_copies_question_bank_id(): void
    {
        $bank = $this->createBankWithQuestions();
        $template = $this->createTemplateWithBank($bank);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $tesStage = $snapshot->stages->firstWhere('key', 'tes_kompetensi');
        $this->assertEquals($bank->id, $tesStage->question_bank_id);
    }

    public function test_stages_without_bank_have_no_questions(): void
    {
        $bank = $this->createBankWithQuestions();
        $template = $this->createTemplateWithBank($bank);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $lamaranStage = $snapshot->stages->firstWhere('key', 'lamaran');
        $this->assertCount(0, $lamaranStage->questions);
    }

    // ── Snapshot immutability ────────────────────────────────────────────

    public function test_editing_source_question_does_not_affect_snapshot(): void
    {
        $bank = $this->createBankWithQuestions();
        $template = $this->createTemplateWithBank($bank);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $sourceQuestion = $bank->questions->first();
        $sourceQuestion->update(['pertanyaan' => 'CHANGED']);

        $tesStage = $snapshot->fresh()->stages->firstWhere('key', 'tes_kompetensi');
        $snapshotQ = $tesStage->questions->firstWhere('urutan', 1);
        $this->assertNotEquals('CHANGED', $snapshotQ->pertanyaan);
    }

    public function test_deleting_source_bank_does_not_affect_snapshot(): void
    {
        $bank = $this->createBankWithQuestions();
        $template = $this->createTemplateWithBank($bank);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $bank->questions()->detach();
        $bank->delete();

        $tesStage = $snapshot->fresh()->stages->firstWhere('key', 'tes_kompetensi');
        $this->assertCount(2, $tesStage->questions);
    }

    // ── Cascade delete ───────────────────────────────────────────────────

    public function test_deleting_snapshot_cascades_to_questions_and_options(): void
    {
        $bank = $this->createBankWithQuestions();
        $template = $this->createTemplateWithBank($bank);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $this->assertDatabaseCount('stage_snapshot_questions', 2);
        $this->assertDatabaseCount('stage_snapshot_options', 2);

        $snapshot->delete();

        $this->assertDatabaseCount('stage_snapshot_questions', 0);
        $this->assertDatabaseCount('stage_snapshot_options', 0);
    }

    // ── Questions ordered by urutan ──────────────────────────────────────

    public function test_questions_ordered_by_urutan(): void
    {
        $stage = WorkflowTemplateSnapshotStage::factory()->create();
        StageSnapshotQuestion::factory()->create([
            'workflow_template_snapshot_stage_id' => $stage->id,
            'urutan' => 3,
        ]);
        StageSnapshotQuestion::factory()->create([
            'workflow_template_snapshot_stage_id' => $stage->id,
            'urutan' => 1,
        ]);

        $questions = $stage->questions()->get();
        $this->assertEquals(1, $questions->first()->urutan);
        $this->assertEquals(3, $questions->last()->urutan);
    }
}
