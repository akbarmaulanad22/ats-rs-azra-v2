<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Vacancy;
use App\Models\VacancyTest;
use App\Models\VacancyTestSnapshot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VacancyTestController extends Controller
{
    public function show(Vacancy $lowongan): View
    {
        Gate::authorize('create', VacancyTest::class);

        $vacancyTest = $lowongan->vacancyTest()->with(['questions.options'])->first();

        $allQuestions = Question::with(['options'])->orderBy('id')->get();

        return view('vacancy-test.show', compact('lowongan', 'vacancyTest', 'allQuestions'));
    }

    public function save(Request $request, Vacancy $lowongan): RedirectResponse
    {
        Gate::authorize('create', VacancyTest::class);

        $validated = $request->validate([
            'batas_waktu_menit' => ['required', 'integer', 'min:5', 'max:480'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
        ]);

        $vacancyTest = $lowongan->vacancyTest()->firstOrNew(['vacancy_id' => $lowongan->id]);
        $vacancyTest->batas_waktu_menit = $validated['batas_waktu_menit'];
        $vacancyTest->save();

        $sync = collect($validated['question_ids'])->mapWithKeys(fn ($id, $index) => [
            $id => ['urutan' => $index + 1],
        ])->toArray();

        $vacancyTest->questions()->sync($sync);

        VacancyTestSnapshot::createFromVacancyTest($vacancyTest->fresh());

        return redirect()->route('lowongan.tes.show', $lowongan)
            ->with('success', 'Konfigurasi tes berhasil disimpan.');
    }
}
