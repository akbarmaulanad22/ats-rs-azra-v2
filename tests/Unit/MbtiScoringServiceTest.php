<?php

namespace Tests\Unit;

use App\Enums\MbtiPole;
use App\Models\MbtiAnswer;
use App\Models\MbtiQuestion;
use App\Models\MbtiResult;
use App\Models\MbtiSubmission;
use App\Services\MbtiScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MbtiScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private MbtiScoringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MbtiScoringService;
    }

    public function test_calculates_pole_scores_from_answers(): void
    {
        $submission = MbtiSubmission::factory()->submitted()->create();

        // Q1: kutub_a=E, pilihan A → E
        $q1 = MbtiQuestion::create(['urutan' => 1, 'dikotomi' => 'EI', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::E, 'pernyataan_b' => 'B']);
        // Q2: kutub_a=E, pilihan B → I (opposite of E)
        $q2 = MbtiQuestion::create(['urutan' => 2, 'dikotomi' => 'EI', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::E, 'pernyataan_b' => 'B']);
        // Q3: kutub_a=T, pilihan A → T
        $q3 = MbtiQuestion::create(['urutan' => 3, 'dikotomi' => 'TF', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::T, 'pernyataan_b' => 'B']);
        // Q4: kutub_a=F, pilihan A → F
        $q4 = MbtiQuestion::create(['urutan' => 4, 'dikotomi' => 'TF', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::F, 'pernyataan_b' => 'B']);

        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q1->id, 'pilihan' => 'A']); // E
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q2->id, 'pilihan' => 'B']); // I
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q3->id, 'pilihan' => 'A']); // T
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q4->id, 'pilihan' => 'A']); // F

        $result = $this->service->calculate($submission);

        $this->assertInstanceOf(MbtiResult::class, $result);
        $this->assertEquals(1, $result->skor_e);
        $this->assertEquals(1, $result->skor_i);
        $this->assertEquals(1, $result->skor_t);
        $this->assertEquals(1, $result->skor_f);
    }

    public function test_type_determined_by_majority_pole_per_dichotomy(): void
    {
        $submission = MbtiSubmission::factory()->submitted()->create();

        // 3×E, 1×I → E wins
        foreach (range(1, 3) as $i) {
            $q = MbtiQuestion::create(['urutan' => $i, 'dikotomi' => 'EI', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::E, 'pernyataan_b' => 'B']);
            MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q->id, 'pilihan' => 'A']);
        }
        $qI = MbtiQuestion::create(['urutan' => 4, 'dikotomi' => 'EI', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::E, 'pernyataan_b' => 'B']);
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $qI->id, 'pilihan' => 'B']); // I

        // 2×S, 3×N → N wins
        foreach (range(5, 6) as $i) {
            $q = MbtiQuestion::create(['urutan' => $i, 'dikotomi' => 'SN', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::S, 'pernyataan_b' => 'B']);
            MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q->id, 'pilihan' => 'A']); // S
        }
        foreach (range(7, 9) as $i) {
            $q = MbtiQuestion::create(['urutan' => $i, 'dikotomi' => 'SN', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::S, 'pernyataan_b' => 'B']);
            MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q->id, 'pilihan' => 'B']); // N
        }

        // All T
        $qT = MbtiQuestion::create(['urutan' => 10, 'dikotomi' => 'TF', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::T, 'pernyataan_b' => 'B']);
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $qT->id, 'pilihan' => 'A']); // T

        // All J
        $qJ = MbtiQuestion::create(['urutan' => 11, 'dikotomi' => 'JP', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::J, 'pernyataan_b' => 'B']);
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $qJ->id, 'pilihan' => 'A']); // J

        $result = $this->service->calculate($submission);

        $this->assertEquals('ENTJ', $result->tipe);
    }

    public function test_tie_resolves_to_second_pole(): void
    {
        $submission = MbtiSubmission::factory()->submitted()->create();

        // Equal E and I → tie → resolves to I
        $q1 = MbtiQuestion::create(['urutan' => 1, 'dikotomi' => 'EI', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::E, 'pernyataan_b' => 'B']);
        $q2 = MbtiQuestion::create(['urutan' => 2, 'dikotomi' => 'EI', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::E, 'pernyataan_b' => 'B']);
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q1->id, 'pilihan' => 'A']); // E
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q2->id, 'pilihan' => 'B']); // I

        // S dominates SN
        $qS = MbtiQuestion::create(['urutan' => 3, 'dikotomi' => 'SN', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::S, 'pernyataan_b' => 'B']);
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $qS->id, 'pilihan' => 'A']); // S

        // T dominates TF
        $qT = MbtiQuestion::create(['urutan' => 4, 'dikotomi' => 'TF', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::T, 'pernyataan_b' => 'B']);
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $qT->id, 'pilihan' => 'A']); // T

        // J dominates JP
        $qJ = MbtiQuestion::create(['urutan' => 5, 'dikotomi' => 'JP', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::J, 'pernyataan_b' => 'B']);
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $qJ->id, 'pilihan' => 'A']); // J

        $result = $this->service->calculate($submission);

        // E=1, I=1 → tie → I
        $this->assertStringStartsWith('I', $result->tipe);
    }

    public function test_preference_strength_calculated_correctly(): void
    {
        $submission = MbtiSubmission::factory()->submitted()->create();

        // 3 E, 1 I → kekuatan_ei = abs(3-1)/(3+1)*100 = 50
        foreach (range(1, 3) as $i) {
            $q = MbtiQuestion::create(['urutan' => $i, 'dikotomi' => 'EI', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::E, 'pernyataan_b' => 'B']);
            MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q->id, 'pilihan' => 'A']); // E
        }
        $q4 = MbtiQuestion::create(['urutan' => 4, 'dikotomi' => 'EI', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::E, 'pernyataan_b' => 'B']);
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q4->id, 'pilihan' => 'B']); // I

        $result = $this->service->calculate($submission);

        $this->assertEquals(50, $result->kekuatan_ei);
    }

    public function test_result_persisted_to_database(): void
    {
        $submission = MbtiSubmission::factory()->submitted()->create();

        $q = MbtiQuestion::create(['urutan' => 1, 'dikotomi' => 'EI', 'pernyataan_a' => 'A', 'kutub_a' => MbtiPole::E, 'pernyataan_b' => 'B']);
        MbtiAnswer::create(['mbti_submission_id' => $submission->id, 'mbti_question_id' => $q->id, 'pilihan' => 'A']); // E

        $this->service->calculate($submission);

        $this->assertDatabaseHas('mbti_results', [
            'mbti_submission_id' => $submission->id,
            'skor_e' => 1,
            'skor_i' => 0,
        ]);
    }
}
