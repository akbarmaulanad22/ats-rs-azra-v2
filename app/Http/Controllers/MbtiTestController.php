<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitMbtiTestRequest;
use App\Logging\LogContext;
use App\Models\MbtiQuestion;
use App\Models\MbtiSubmission;
use App\Services\ApplicationPipelineService;
use App\Services\MbtiScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MbtiTestController extends Controller
{
    public function __construct(
        private readonly MbtiScoringService $scoringService,
        private readonly ApplicationPipelineService $pipelineService,
    ) {}

    public function show(string $token): View|RedirectResponse
    {
        $submission = MbtiSubmission::with([
            'application.vacancy',
        ])->where('token', $token)->firstOrFail();

        if ($submission->isSubmitted()) {
            return view('mbti.show', compact('submission'));
        }

        if ($submission->started_at === null) {
            $submission->update(['started_at' => now()]);
        }

        $questions = MbtiQuestion::orderBy('urutan')->get();

        return view('mbti.show', compact('submission', 'questions'));
    }

    public function submit(SubmitMbtiTestRequest $request, string $token): RedirectResponse
    {
        $submission = MbtiSubmission::with([
            'application.candidate',
            'application.vacancy',
        ])->where('token', $token)->firstOrFail();

        if ($submission->isSubmitted()) {
            return redirect()->route('tes-mbti.show', $token);
        }

        $jawaban = $request->validated('jawaban');

        return $this->doSubmit($submission, $jawaban);
    }

    /** @param array<int|string, string> $jawaban */
    private function doSubmit(MbtiSubmission $submission, array $jawaban): RedirectResponse
    {
        DB::transaction(function () use ($submission, $jawaban): void {
            $locked = MbtiSubmission::lockForUpdate()->findOrFail($submission->id);

            if ($locked->isSubmitted()) {
                return;
            }

            $questions = MbtiQuestion::orderBy('urutan')->get();

            foreach ($questions as $question) {
                $locked->answers()->create([
                    'mbti_question_id' => $question->id,
                    'pilihan' => strtoupper($jawaban[$question->id]),
                ]);
            }

            $locked->update(['submitted_at' => now()]);

            $this->scoringService->calculate($locked);

            $locked->load('application');
            $this->pipelineService->advance($locked->application);
        });

        Log::info('MBTI test submitted', array_merge(LogContext::make(), [
            'submission_id' => $submission->id,
            'application_id' => $submission->application_id,
        ]));

        return redirect()->route('tes-mbti.show', $submission->token);
    }
}
