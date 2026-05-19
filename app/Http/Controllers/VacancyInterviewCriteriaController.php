<?php

namespace App\Http\Controllers;

use App\Models\InterviewTemplate;
use App\Models\Vacancy;
use App\Models\VacancyInterviewTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VacancyInterviewCriteriaController extends Controller
{
    public function show(Vacancy $lowongan): View
    {
        Gate::authorize('manageInterviewCriteria', $lowongan);

        $lowongan->load('workflowTemplateSnapshot.stages');

        $wawancaraStages = $lowongan->workflowTemplateSnapshot->stages
            ->filter(fn ($s) => str_starts_with($s->key, 'wawancara_'));

        $assigned = VacancyInterviewTemplate::where('vacancy_id', $lowongan->id)
            ->with('interviewTemplate')
            ->get()
            ->groupBy('stage_key');

        $templates = InterviewTemplate::orderBy('nama')->get();

        return view('vacancy-interview-criteria.show', [
            'lowongan' => $lowongan,
            'wawancaraStages' => $wawancaraStages,
            'assigned' => $assigned,
            'templates' => $templates,
        ]);
    }

    public function save(Request $request, Vacancy $lowongan): RedirectResponse
    {
        Gate::authorize('manageInterviewCriteria', $lowongan);

        $lowongan->load('workflowTemplateSnapshot.stages');

        $validStageKeys = $lowongan->workflowTemplateSnapshot->stages
            ->filter(fn ($s) => str_starts_with($s->key, 'wawancara_'))
            ->pluck('key')
            ->toArray();

        $validated = $request->validate([
            'assignments' => ['nullable', 'array'],
            'assignments.*' => ['array'],
            'assignments.*.*' => ['integer', 'exists:interview_templates,id'],
        ]);

        DB::transaction(function () use ($lowongan, $validated, $validStageKeys): void {
            VacancyInterviewTemplate::where('vacancy_id', $lowongan->id)->delete();

            foreach ($validated['assignments'] ?? [] as $stageKey => $templateIds) {
                if (! in_array($stageKey, $validStageKeys, true)) {
                    continue;
                }

                foreach (array_unique($templateIds) as $templateId) {
                    VacancyInterviewTemplate::create([
                        'vacancy_id' => $lowongan->id,
                        'interview_template_id' => $templateId,
                        'stage_key' => $stageKey,
                    ]);
                }
            }
        });

        return redirect()->route('lowongan.kriteria-wawancara.show', $lowongan)
            ->with('success', 'Template wawancara berhasil disimpan.');
    }
}
