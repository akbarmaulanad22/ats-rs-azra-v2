<?php

namespace App\Http\Controllers;

use App\Models\DiscQuestion;
use App\Models\DiscSubmission;
use App\Services\ApplicationPipelineService;
use App\Services\DiscScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DiscTestController extends Controller
{
    public function __construct(
        private readonly DiscScoringService $scoringService,
        private readonly ApplicationPipelineService $pipelineService,
    ) {}

    public function show(string $token): View|RedirectResponse
    {
        $submission = DiscSubmission::with([
            'application.vacancy',
        ])->where('token', $token)->firstOrFail();

        if ($submission->isSubmitted()) {
            return view('disc.show', compact('submission'));
        }

        if ($submission->started_at === null) {
            $submission->update(['started_at' => now()]);
        }

        $questions = DiscQuestion::with('words')->orderBy('urutan')->get();

        return view('disc.show', compact('submission', 'questions'));
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $submission = DiscSubmission::with([
            'application.candidate',
            'application.vacancy',
        ])->where('token', $token)->firstOrFail();

        if ($submission->isSubmitted()) {
            return redirect()->route('tes-disc.show', $token);
        }

        $most = $request->input('most', []);
        $least = $request->input('least', []);

        return $this->doSubmit($submission, $most, $least);
    }

    private function doSubmit(DiscSubmission $submission, array $most, array $least): RedirectResponse
    {
        DB::transaction(function () use ($submission, $most, $least): void {
            $locked = DiscSubmission::lockForUpdate()->findOrFail($submission->id);

            if ($locked->isSubmitted()) {
                return;
            }

            $questions = DiscQuestion::with('words')->orderBy('urutan')->get();

            foreach ($questions as $question) {
                $mostWordId = isset($most[$question->id]) ? (int) $most[$question->id] : null;
                $leastWordId = isset($least[$question->id]) ? (int) $least[$question->id] : null;

                if ($mostWordId === null || $leastWordId === null) {
                    continue;
                }

                $wordIds = $question->words->pluck('id')->all();

                if (! in_array($mostWordId, $wordIds) || ! in_array($leastWordId, $wordIds)) {
                    continue;
                }

                if ($mostWordId === $leastWordId) {
                    continue;
                }

                $submission->answers()->create([
                    'disc_question_id' => $question->id,
                    'most_disc_word_id' => $mostWordId,
                    'least_disc_word_id' => $leastWordId,
                ]);
            }

            $submission->update(['submitted_at' => now()]);

            $this->scoringService->calculate($submission);
        });

        $submission->load('application');
        $this->pipelineService->advance($submission->application);

        return redirect()->route('tes-disc.show', $submission->token);
    }
}
