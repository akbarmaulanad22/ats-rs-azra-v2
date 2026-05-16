<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Question::class);

        $unitFilter = $request->query('unit_id');
        $typeFilter = $request->query('tipe');

        $questionsQuery = Question::with(['unit', 'options'])
            ->latest();

        if ($unitFilter) {
            $questionsQuery->where('unit_id', $unitFilter);
        }

        if ($typeFilter && in_array($typeFilter, ['mc', 'essay'], true)) {
            $questionsQuery->where('tipe', $typeFilter);
        }

        $questions = $questionsQuery->paginate(20)->withQueryString();
        $units = Unit::orderBy('nama')->get();

        return view('questions.index', compact('questions', 'units', 'unitFilter', 'typeFilter'));
    }

    public function create(): View
    {
        Gate::authorize('create', Question::class);

        $units = Unit::orderBy('nama')->get();

        return view('questions.create', compact('units'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Question::class);

        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'tipe' => ['required', Rule::enum(QuestionType::class)],
            'pertanyaan' => ['required', 'string'],
            'nilai_poin' => ['required', 'integer', 'min:1', 'max:100'],
            'options' => ['required_if:tipe,mc', 'array', 'min:2'],
            'options.*.teks_opsi' => ['required_if:tipe,mc', 'string'],
            'options.*.is_correct' => ['boolean'],
            'correct_option' => ['required_if:tipe,mc', 'integer'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $question = Question::create([
                'unit_id' => $validated['unit_id'],
                'tipe' => $validated['tipe'],
                'pertanyaan' => $validated['pertanyaan'],
                'nilai_poin' => $validated['nilai_poin'],
            ]);

            if ($validated['tipe'] === QuestionType::Mc->value) {
                $correctIndex = (int) $request->input('correct_option', 0);
                foreach ($validated['options'] as $index => $option) {
                    $question->options()->create([
                        'teks_opsi' => $option['teks_opsi'],
                        'is_correct' => $index === $correctIndex,
                    ]);
                }
            }
        });

        return redirect()->route('bank-soal.index')
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit(Question $bankSoal): View
    {
        Gate::authorize('update', $bankSoal);

        $bankSoal->load('options');
        $units = Unit::orderBy('nama')->get();

        return view('questions.edit', ['question' => $bankSoal, 'units' => $units]);
    }

    public function update(Request $request, Question $bankSoal): RedirectResponse
    {
        Gate::authorize('update', $bankSoal);

        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'tipe' => ['required', Rule::enum(QuestionType::class)],
            'pertanyaan' => ['required', 'string'],
            'nilai_poin' => ['required', 'integer', 'min:1', 'max:100'],
            'options' => ['required_if:tipe,mc', 'array', 'min:2'],
            'options.*.teks_opsi' => ['required_if:tipe,mc', 'string'],
            'correct_option' => ['required_if:tipe,mc', 'integer'],
        ]);

        DB::transaction(function () use ($validated, $request, $bankSoal): void {
            $bankSoal->update([
                'unit_id' => $validated['unit_id'],
                'tipe' => $validated['tipe'],
                'pertanyaan' => $validated['pertanyaan'],
                'nilai_poin' => $validated['nilai_poin'],
            ]);

            if ($validated['tipe'] === QuestionType::Mc->value) {
                $bankSoal->options()->delete();
                $correctIndex = (int) $request->input('correct_option', 0);
                foreach ($validated['options'] as $index => $option) {
                    $bankSoal->options()->create([
                        'teks_opsi' => $option['teks_opsi'],
                        'is_correct' => $index === $correctIndex,
                    ]);
                }
            } else {
                $bankSoal->options()->delete();
            }
        });

        return redirect()->route('bank-soal.index')
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Question $bankSoal): RedirectResponse
    {
        Gate::authorize('delete', $bankSoal);

        $bankSoal->delete();

        return redirect()->route('bank-soal.index')
            ->with('success', 'Soal berhasil dihapus.');
    }
}
