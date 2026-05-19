<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Models\QuestionBankTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuestionBankTemplateController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', QuestionBankTemplate::class);

        $query = QuestionBankTemplate::withCount('questions')
            ->latest();

        if ($search = $request->query('q')) {
            $query->where('nama', 'ilike', "%{$search}%");
        }

        $templates = $query->paginate(20)->withQueryString();

        return view('question-bank-templates.index', compact('templates'));
    }

    public function create(): View
    {
        Gate::authorize('create', QuestionBankTemplate::class);

        return view('question-bank-templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', QuestionBankTemplate::class);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:question_bank_templates,nama'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.tipe' => ['required', Rule::enum(QuestionType::class)],
            'questions.*.pertanyaan' => ['required', 'string'],
            'questions.*.nilai_poin' => ['required', 'integer', 'min:1', 'max:100'],
            'questions.*.options' => ['required_if:questions.*.tipe,mc', 'array', 'min:2'],
            'questions.*.options.*.teks_opsi' => ['required_if:questions.*.tipe,mc', 'string'],
            'questions.*.correct_option' => ['required_if:questions.*.tipe,mc', 'integer'],
        ]);

        DB::transaction(function () use ($validated): void {
            $template = QuestionBankTemplate::create([
                'nama' => $validated['nama'],
            ]);

            foreach ($validated['questions'] as $index => $questionData) {
                $question = $template->questions()->create([
                    'tipe' => $questionData['tipe'],
                    'pertanyaan' => $questionData['pertanyaan'],
                    'nilai_poin' => $questionData['nilai_poin'],
                    'urutan' => $index + 1,
                ]);

                if ($questionData['tipe'] === QuestionType::Mc->value) {
                    $correctIndex = (int) ($questionData['correct_option'] ?? 0);
                    foreach ($questionData['options'] as $optIndex => $option) {
                        $question->options()->create([
                            'teks_opsi' => $option['teks_opsi'],
                            'is_correct' => $optIndex === $correctIndex,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('template-bank-soal.index')
            ->with('success', 'Template bank soal berhasil dibuat.');
    }

    public function edit(QuestionBankTemplate $templateBankSoal): View
    {
        Gate::authorize('update', $templateBankSoal);

        $templateBankSoal->load('questions.options');

        return view('question-bank-templates.edit', ['template' => $templateBankSoal]);
    }

    public function update(Request $request, QuestionBankTemplate $templateBankSoal): RedirectResponse
    {
        Gate::authorize('update', $templateBankSoal);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('question_bank_templates', 'nama')->ignore($templateBankSoal->id)],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.tipe' => ['required', Rule::enum(QuestionType::class)],
            'questions.*.pertanyaan' => ['required', 'string'],
            'questions.*.nilai_poin' => ['required', 'integer', 'min:1', 'max:100'],
            'questions.*.options' => ['required_if:questions.*.tipe,mc', 'array', 'min:2'],
            'questions.*.options.*.teks_opsi' => ['required_if:questions.*.tipe,mc', 'string'],
            'questions.*.correct_option' => ['required_if:questions.*.tipe,mc', 'integer'],
        ]);

        DB::transaction(function () use ($validated, $templateBankSoal): void {
            $templateBankSoal->update(['nama' => $validated['nama']]);

            $templateBankSoal->questions()->each(function ($question) {
                $question->options()->delete();
            });
            $templateBankSoal->questions()->delete();

            foreach ($validated['questions'] as $index => $questionData) {
                $question = $templateBankSoal->questions()->create([
                    'tipe' => $questionData['tipe'],
                    'pertanyaan' => $questionData['pertanyaan'],
                    'nilai_poin' => $questionData['nilai_poin'],
                    'urutan' => $index + 1,
                ]);

                if ($questionData['tipe'] === QuestionType::Mc->value) {
                    $correctIndex = (int) ($questionData['correct_option'] ?? 0);
                    foreach ($questionData['options'] as $optIndex => $option) {
                        $question->options()->create([
                            'teks_opsi' => $option['teks_opsi'],
                            'is_correct' => $optIndex === $correctIndex,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('template-bank-soal.index')
            ->with('success', 'Template bank soal berhasil diperbarui.');
    }

    public function destroy(QuestionBankTemplate $templateBankSoal): RedirectResponse
    {
        Gate::authorize('delete', $templateBankSoal);

        $templateBankSoal->delete();

        return redirect()->route('template-bank-soal.index')
            ->with('success', 'Template bank soal berhasil dihapus.');
    }
}
