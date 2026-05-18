<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Http\Requests\CvScreeningDecisionRequest;
use App\Models\TestAnswer;
use App\Models\TestSubmission;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TestReviewController extends Controller
{
    public function __construct(private readonly ApplicationPipelineService $pipelineService) {}

    public function index(Vacancy $lowongan): View
    {
        $vacancyTest = $lowongan->vacancyTest()->firstOrFail();
        Gate::authorize('reviewEssay', $vacancyTest);

        $latestSnapshot = $vacancyTest->latestSnapshot;

        $submissions = TestSubmission::with(['application.candidate', 'answers'])
            ->whereHas('snapshot', fn ($q) => $q->where('vacancy_test_id', $vacancyTest->id))
            ->whereNotNull('submitted_at')
            ->get();

        return view('test-review.index', compact('lowongan', 'vacancyTest', 'latestSnapshot', 'submissions'));
    }

    public function show(Vacancy $lowongan, TestSubmission $submission): View
    {
        $vacancyTest = $lowongan->vacancyTest()->firstOrFail();
        Gate::authorize('reviewEssay', $vacancyTest);

        abort_if($submission->snapshot->vacancy_test_id !== $vacancyTest->id, 404);

        $submission->load([
            'answers.question.options',
            'answers.selectedOption',
            'application.candidate',
            'application.stages',
            'snapshot',
        ]);

        $allReviewed = $submission->answers->every(fn ($a) => $a->is_reviewed);
        $currentStage = $submission->application->stages->firstWhere('key', 'tes_kompetensi');

        return view('test-review.show', compact('lowongan', 'vacancyTest', 'submission', 'allReviewed', 'currentStage'));
    }

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

    public function decide(CvScreeningDecisionRequest $request, Vacancy $lowongan, TestSubmission $submission): RedirectResponse
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
            ->route('lowongan.tes.ulasan.index', $lowongan)
            ->with('success', "Kandidat berhasil {$label}.");
    }
}
