<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Http\Requests\CompetencyTestDecisionRequest;
use App\Models\TestAnswer;
use App\Models\TestSubmission;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TestReviewController extends Controller
{
    public function __construct(private readonly ApplicationPipelineService $pipelineService) {}

    public function scoreEssay(Request $request, Vacancy $lowongan, TestAnswer $answer): RedirectResponse
    {
        $vacancyTest = $lowongan->vacancyTest()->firstOrFail();
        Gate::authorize('reviewEssay', $vacancyTest);

        abort_if($answer->question->tipe !== QuestionType::Essay, 422);
        abort_if($answer->submission->snapshot->vacancy_test_id !== $vacancyTest->id, 404);

        $validated = $request->validate([
            'skor' => ['required', 'integer', 'min:0', 'max:'.$answer->question->nilai_poin],
        ]);

        $answer->update([
            'skor' => $validated['skor'],
            'is_reviewed' => true,
        ]);

        $submission = $answer->submission;
        $allAnswers = $submission->answers()->with('question')->get();

        if ($allAnswers->every(fn ($a) => $a->is_reviewed)) {
            $submission->update(['total_skor' => $allAnswers->sum('skor')]);
        }

        return back()->with('success', 'Nilai esai berhasil disimpan.');
    }

    public function decide(CompetencyTestDecisionRequest $request, Vacancy $lowongan, TestSubmission $submission): RedirectResponse
    {
        $vacancyTest = $lowongan->vacancyTest()->firstOrFail();
        Gate::authorize('decide', $vacancyTest);

        abort_if($submission->snapshot->vacancy_test_id !== $vacancyTest->id, 404);

        $submission->load('answers');
        $allReviewed = $submission->answers->every(fn ($a) => $a->is_reviewed);

        if (! $allReviewed) {
            return back()->withErrors(['keputusan' => 'Semua jawaban harus dinilai sebelum mengambil keputusan.']);
        }

        $application = $submission->application;
        $catatan = $request->input('catatan');
        $keputusan = $request->input('keputusan');

        try {
            DB::transaction(function () use ($application, $catatan, $keputusan): void {
                $testStage = $application->stages()->where('key', 'tes_kompetensi')->first();
                $testStage?->update(['catatan' => $catatan]);

                match ($keputusan) {
                    'lulus' => $this->pipelineService->advance($application),
                    'gagal' => $this->pipelineService->fail($application),
                    'reserved' => $this->pipelineService->reserve($application),
                };
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['keputusan' => $e->getMessage()]);
        }

        $label = match ($keputusan) {
            'lulus' => 'diloloskan ke tahap berikutnya',
            'gagal' => 'ditolak dari pipeline',
            'reserved' => 'ditangguhkan',
        };

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', "Kandidat berhasil {$label}.");
    }
}
