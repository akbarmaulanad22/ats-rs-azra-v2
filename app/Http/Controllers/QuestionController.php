<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Models\Question;
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

        $typeFilter = $request->query('tipe');

        $questionsQuery = Question::with(['options'])
            ->latest();

        if ($typeFilter && in_array($typeFilter, ['mc', 'essay'], true)) {
            $questionsQuery->where('tipe', $typeFilter);
        }

        $questions = $questionsQuery->paginate(20)->withQueryString();

        return view('questions.index', compact('questions', 'typeFilter'));
    }

    public function create(): View
    {
        Gate::authorize('create', Question::class);

        return view('questions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Question::class);

        $validated = $request->validate([
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

        return view('questions.edit', ['question' => $bankSoal]);
    }

    public function update(Request $request, Question $bankSoal): RedirectResponse
    {
        Gate::authorize('update', $bankSoal);

        $validated = $request->validate([
            'tipe' => ['required', Rule::enum(QuestionType::class)],
            'pertanyaan' => ['required', 'string'],
            'nilai_poin' => ['required', 'integer', 'min:1', 'max:100'],
            'options' => ['required_if:tipe,mc', 'array', 'min:2'],
            'options.*.teks_opsi' => ['required_if:tipe,mc', 'string'],
            'correct_option' => ['required_if:tipe,mc', 'integer'],
        ]);

        DB::transaction(function () use ($validated, $request, $bankSoal): void {
            $bankSoal->update([
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
