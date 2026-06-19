<?php

namespace App\Http\Controllers;

use App\Models\JobTemplate;
use App\Models\QuestionBankTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JobTemplateTestController extends Controller
{
    public function show(JobTemplate $templateLowongan): View
    {
        Gate::authorize('manageTest', $templateLowongan);

        $templateLowongan->load('unit');
        $jobTemplateTest = $templateLowongan->jobTemplateTest()->with('questions.options')->first();

        $templates = QuestionBankTemplate::withCount('questions')
            ->with('questions.options')
            ->orderBy('nama')
            ->get();

        $templateQuestions = $templates->keyBy('id')
            ->map(fn ($t) => $t->questions->map(fn ($q) => [
                'id' => $q->id,
                'tipe' => $q->tipe->value,
                'tipe_label' => $q->tipe->label(),
                'pertanyaan' => $q->pertanyaan,
                'nilai_poin' => $q->nilai_poin,
            ])->values());

        return view('job-template-test.show', compact('templateLowongan', 'jobTemplateTest', 'templates', 'templateQuestions'));
    }

    public function save(Request $request, JobTemplate $templateLowongan): RedirectResponse
    {
        Gate::authorize('manageTest', $templateLowongan);

        $validated = $request->validate([
            'batas_waktu_menit' => ['required', 'integer', 'min:5', 'max:480'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
        ]);

        $jobTemplateTest = $templateLowongan->jobTemplateTest()->firstOrNew(['job_template_id' => $templateLowongan->id]);
        $jobTemplateTest->batas_waktu_menit = $validated['batas_waktu_menit'];
        $jobTemplateTest->save();

        $sync = collect($validated['question_ids'])->mapWithKeys(fn ($id, $index) => [
            $id => ['urutan' => $index + 1],
        ])->toArray();

        $jobTemplateTest->questions()->sync($sync);

        return redirect()->route('template-lowongan.tes.show', $templateLowongan)
            ->with('success', 'Konfigurasi tes berhasil disimpan.');
    }
}
