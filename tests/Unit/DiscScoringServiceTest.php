<?php

namespace Tests\Unit;

use App\Enums\DiscDimension;
use App\Models\DiscAnswer;
use App\Models\DiscQuestion;
use App\Models\DiscQuestionWord;
use App\Models\DiscResult;
use App\Models\DiscSubmission;
use App\Services\DiscScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private DiscScoringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DiscScoringService;
    }

    public function test_calculates_dimension_scores_from_most_selections(): void
    {
        // Create 4 questions with known word-dimension mapping
        // Q1: most=D, Q2: most=I, Q3: most=D, Q4: most=S
        $submission = DiscSubmission::factory()->submitted()->create();

        $questions = [];
        foreach (range(1, 4) as $i) {
            $q = DiscQuestion::create(['urutan' => $i]);
            $words = [];
            foreach ([DiscDimension::D, DiscDimension::I, DiscDimension::S, DiscDimension::C] as $dim) {
                $words[$dim->value] = DiscQuestionWord::create([
                    'disc_question_id' => $q->id,
                    'teks' => fake()->word(),
                    'dimensi' => $dim,
                ]);
            }
            $questions[] = [$q, $words];
        }

        // Q1: most=D, least=I
        DiscAnswer::create([
            'disc_submission_id' => $submission->id,
            'disc_question_id' => $questions[0][0]->id,
            'most_disc_word_id' => $questions[0][1]['D']->id,
            'least_disc_word_id' => $questions[0][1]['I']->id,
        ]);

        // Q2: most=I, least=D
        DiscAnswer::create([
            'disc_submission_id' => $submission->id,
            'disc_question_id' => $questions[1][0]->id,
            'most_disc_word_id' => $questions[1][1]['I']->id,
            'least_disc_word_id' => $questions[1][1]['D']->id,
        ]);

        // Q3: most=D, least=C
        DiscAnswer::create([
            'disc_submission_id' => $submission->id,
            'disc_question_id' => $questions[2][0]->id,
            'most_disc_word_id' => $questions[2][1]['D']->id,
            'least_disc_word_id' => $questions[2][1]['C']->id,
        ]);

        // Q4: most=S, least=D
        DiscAnswer::create([
            'disc_submission_id' => $submission->id,
            'disc_question_id' => $questions[3][0]->id,
            'most_disc_word_id' => $questions[3][1]['S']->id,
            'least_disc_word_id' => $questions[3][1]['D']->id,
        ]);

        $result = $this->service->calculate($submission);

        $this->assertInstanceOf(DiscResult::class, $result);
        $this->assertEquals(2, $result->skor_d); // D selected as most in Q1 and Q3
        $this->assertEquals(1, $result->skor_i); // I selected as most in Q2
        $this->assertEquals(1, $result->skor_s); // S selected as most in Q4
        $this->assertEquals(0, $result->skor_c); // C never selected as most
    }

    public function test_primary_type_is_highest_scoring_dimension(): void
    {
        $submission = DiscSubmission::factory()->submitted()->create();

        foreach (range(1, 3) as $i) {
            $q = DiscQuestion::create(['urutan' => $i]);
            $words = [];
            foreach ([DiscDimension::D, DiscDimension::I, DiscDimension::S, DiscDimension::C] as $dim) {
                $words[$dim->value] = DiscQuestionWord::create([
                    'disc_question_id' => $q->id,
                    'teks' => fake()->word(),
                    'dimensi' => $dim,
                ]);
            }

            // All 3 most selections = C (primary C)
            DiscAnswer::create([
                'disc_submission_id' => $submission->id,
                'disc_question_id' => $q->id,
                'most_disc_word_id' => $words['C']->id,
                'least_disc_word_id' => $words['D']->id,
            ]);
        }

        // Add 1 I selection (secondary I)
        $q4 = DiscQuestion::create(['urutan' => 4]);
        $words4 = [];
        foreach ([DiscDimension::D, DiscDimension::I, DiscDimension::S, DiscDimension::C] as $dim) {
            $words4[$dim->value] = DiscQuestionWord::create([
                'disc_question_id' => $q4->id,
                'teks' => fake()->word(),
                'dimensi' => $dim,
            ]);
        }
        DiscAnswer::create([
            'disc_submission_id' => $submission->id,
            'disc_question_id' => $q4->id,
            'most_disc_word_id' => $words4['I']->id,
            'least_disc_word_id' => $words4['D']->id,
        ]);

        $result = $this->service->calculate($submission);

        $this->assertEquals(DiscDimension::C, $result->tipe_primer);
        $this->assertEquals(DiscDimension::I, $result->tipe_sekunder);
    }

    public function test_result_persisted_to_database(): void
    {
        $submission = DiscSubmission::factory()->submitted()->create();
        $q = DiscQuestion::create(['urutan' => 1]);
        $wordD = DiscQuestionWord::create(['disc_question_id' => $q->id, 'teks' => 'Tegas', 'dimensi' => DiscDimension::D]);
        $wordI = DiscQuestionWord::create(['disc_question_id' => $q->id, 'teks' => 'Ramah', 'dimensi' => DiscDimension::I]);

        DiscAnswer::create([
            'disc_submission_id' => $submission->id,
            'disc_question_id' => $q->id,
            'most_disc_word_id' => $wordD->id,
            'least_disc_word_id' => $wordI->id,
        ]);

        $this->service->calculate($submission);

        $this->assertDatabaseHas('disc_results', [
            'disc_submission_id' => $submission->id,
            'skor_d' => 1,
            'skor_i' => 0,
            'skor_s' => 0,
            'skor_c' => 0,
            'tipe_primer' => 'D',
        ]);
    }
}
