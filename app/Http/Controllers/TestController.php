<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Models\TestSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TestController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $submission = TestSubmission::with([
            'vacancyTest.questions.options',
            'application.vacancy',
        ])->where('token', $token)->firstOrFail();

        if ($submission->isSubmitted()) {
            $questions = $submission->vacancyTest->questions;

            return view('test.show', compact('submission', 'questions'));
        }

        if ($submission->started_at === null) {
            $submission->update(['started_at' => now()]);
        }

        if ($submission->isExpired()) {
            return $this->doSubmit($submission, []);
        }

        $questions = $submission->vacancyTest->questions;

        return view('test.show', compact('submission', 'questions'));
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $submission = TestSubmission::with([
            'vacancyTest.questions.options',
            'application.candidate',
            'application.vacancy',
        ])->where('token', $token)->firstOrFail();

        if ($submission->isSubmitted()) {
            return redirect()->route('tes.show', $token);
        }

        $answers = $request->input('answers', []);

        return $this->doSubmit($submission, $answers);
    }

    private function doSubmit(TestSubmission $submission, array $answers): RedirectResponse
    {
        DB::transaction(function () use ($submission, $answers): void {
            $locked = TestSubmission::lockForUpdate()->findOrFail($submission->id);

            if ($locked->isSubmitted()) {
                return;
            }

            $totalSkor = 0;
            $questions = $submission->vacancyTest->questions;

            foreach ($questions as $question) {
                $selectedOptionId = $answers[$question->id] ?? null;

                $skor = null;
                $questionOptionId = null;
                $jawabanTeks = null;

                if ($question->tipe === QuestionType::Mc) {
                    $questionOptionId = $selectedOptionId ? (int) $selectedOptionId : null;
                    $correctOption = $question->correctOption();

                    if ($correctOption && $questionOptionId === $correctOption->id) {
                        $skor = $question->nilai_poin;
                    } else {
                        $skor = 0;
                    }

                    $totalSkor += $skor;
                } else {
                    $jawabanTeks = $answers[$question->id] ?? null;
                }

                $submission->answers()->create([
                    'question_id' => $question->id,
                    'question_option_id' => $questionOptionId,
                    'jawaban_teks' => $jawabanTeks,
                    'skor' => $skor,
                    'is_reviewed' => $question->tipe === QuestionType::Mc,
                ]);
            }

            $submission->update([
                'submitted_at' => now(),
                'total_skor' => $totalSkor,
            ]);
        });

        return redirect()->route('tes.show', $submission->token);
    }
}
