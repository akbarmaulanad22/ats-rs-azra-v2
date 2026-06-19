<?php

namespace App\Http\Controllers;

use App\Models\InterviewTemplate;
use App\Models\JobTemplate;
use App\Models\JobTemplateInterviewTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JobTemplateInterviewTemplateController extends Controller
{
    public function show(JobTemplate $templateLowongan): View
    {
        Gate::authorize('manageInterviewTemplates', $templateLowongan);

        $templateLowongan->load('unit', 'workflowTemplate.stages');

        $wawancaraStages = $templateLowongan->workflowTemplate->stages
            ->filter(fn ($s) => str_starts_with($s->key, 'wawancara_'));

        $assigned = JobTemplateInterviewTemplate::where('job_template_id', $templateLowongan->id)
            ->with('interviewTemplate')
            ->get()
            ->groupBy('stage_key');

        $templates = InterviewTemplate::orderBy('nama')->get();

        return view('job-template-interview-templates.show', [
            'templateLowongan' => $templateLowongan,
            'wawancaraStages' => $wawancaraStages,
            'assigned' => $assigned,
            'templates' => $templates,
        ]);
    }

    public function save(Request $request, JobTemplate $templateLowongan): RedirectResponse
    {
        Gate::authorize('manageInterviewTemplates', $templateLowongan);

        $templateLowongan->load('workflowTemplate.stages');

        $validStageKeys = $templateLowongan->workflowTemplate->stages
            ->filter(fn ($s) => str_starts_with($s->key, 'wawancara_'))
            ->pluck('key')
            ->toArray();

        $validated = $request->validate([
            'assignments' => ['nullable', 'array'],
            'assignments.*' => ['array'],
            'assignments.*.*' => ['integer', 'exists:interview_templates,id'],
        ]);

        DB::transaction(function () use ($templateLowongan, $validated, $validStageKeys): void {
            JobTemplateInterviewTemplate::where('job_template_id', $templateLowongan->id)->delete();

            foreach ($validated['assignments'] ?? [] as $stageKey => $templateIds) {
                if (! in_array($stageKey, $validStageKeys, true)) {
                    continue;
                }

                foreach (array_unique($templateIds) as $templateId) {
                    JobTemplateInterviewTemplate::create([
                        'job_template_id' => $templateLowongan->id,
                        'interview_template_id' => $templateId,
                        'stage_key' => $stageKey,
                    ]);
                }
            }
        });

        return redirect()->route('template-lowongan.template-wawancara.show', $templateLowongan)
            ->with('success', 'Template wawancara berhasil disimpan.');
    }
}
