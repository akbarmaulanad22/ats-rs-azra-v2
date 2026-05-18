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
            'snapshot.questions.options',
            'application.vacancy',
        ])->where('token', $token)->firstOrFail();

        if ($submission->isSubmitted()) {
            $questions = $submission->snapshot->questions;

            return view('test.show', compact('submission', 'questions'));
        }

        if ($submission->started_at === null) {
            $submission->update(['started_at' => now()]);
        }

        if ($submission->isExpired()) {
            return $this->doSubmit($submission, []);
        }

        $questions = $submission->snapshot->questions;

        return view('test.show', compact('submission', 'questions'));
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $submission = TestSubmission::with([
            'snapshot.questions.options',
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
            $questions = $submission->snapshot->questions;

            foreach ($questions as $question) {
                $selectedOptionId = $answers[$question->id] ?? null;

                $skor = null;
                $snapshotOptionId = null;
                $jawabanTeks = null;

                if ($question->tipe === QuestionType::Mc) {
                    $snapshotOptionId = $selectedOptionId ? (int) $selectedOptionId : null;
                    $correctOption = $question->options->firstWhere('is_correct', true);

                    if ($correctOption && $snapshotOptionId === $correctOption->id) {
                        $skor = $question->nilai_poin;
                    } else {
                        $skor = 0;
                    }

                    $totalSkor += $skor;
                } else {
                    $jawabanTeks = $answers[$question->id] ?? null;
                }

                $submission->answers()->create([
                    'vacancy_test_snapshot_question_id' => $question->id,
                    'vacancy_test_snapshot_option_id' => $snapshotOptionId,
                    'jawaban_teks' => $jawabanTeks,
                    'skor' => $skor,
                    'is_reviewed' => $question->tipe === QuestionType::Mc,
                ]);
            }

            $hasEssay = $questions->contains(fn ($q) => $q->tipe === QuestionType::Essay);

            $submission->update([
                'submitted_at' => now(),
                'total_skor' => $hasEssay ? null : $totalSkor,
            ]);
        });

        return redirect()->route('tes.show', $submission->token);
    }
}
