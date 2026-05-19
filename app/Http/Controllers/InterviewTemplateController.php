<?php

namespace App\Http\Controllers;

use App\Enums\InterviewTemplateType;
use App\Models\InterviewTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InterviewTemplateController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', InterviewTemplate::class);

        $query = InterviewTemplate::withCount('items')->latest();

        if ($search = $request->query('q')) {
            $query->where('nama', 'like', "%{$search}%");
        }

        $templates = $query->paginate(20)->withQueryString();

        return view('interview-templates.index', compact('templates'));
    }

    public function create(): View
    {
        Gate::authorize('create', InterviewTemplate::class);

        return view('interview-templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', InterviewTemplate::class);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:interview_templates,nama'],
            'tipe' => ['required', Rule::enum(InterviewTemplateType::class)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.teks' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated): void {
            $template = InterviewTemplate::create([
                'nama' => $validated['nama'],
                'tipe' => $validated['tipe'],
            ]);

            foreach ($validated['items'] as $index => $itemData) {
                $template->items()->create([
                    'teks' => $itemData['teks'],
                    'urutan' => $index + 1,
                ]);
            }
        });

        return redirect()->route('template-wawancara.index')
            ->with('success', 'Template wawancara berhasil dibuat.');
    }

    public function edit(InterviewTemplate $templateWawancara): View
    {
        Gate::authorize('update', $templateWawancara);

        $templateWawancara->load('items');

        return view('interview-templates.edit', ['template' => $templateWawancara]);
    }

    public function update(Request $request, InterviewTemplate $templateWawancara): RedirectResponse
    {
        Gate::authorize('update', $templateWawancara);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('interview_templates', 'nama')->ignore($templateWawancara->id)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', Rule::exists('interview_template_items', 'id')->where('interview_template_id', $templateWawancara->id)],
            'items.*.teks' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $templateWawancara): void {
            $templateWawancara->update(['nama' => $validated['nama']]);

            $submittedIds = collect($validated['items'])->pluck('id')->filter()->all();
            $templateWawancara->items()->whereNotIn('id', $submittedIds)->delete();

            $existingItems = $templateWawancara->items()->whereIn('id', $submittedIds)->get()->keyBy('id');

            foreach ($validated['items'] as $index => $itemData) {
                $attributes = [
                    'teks' => $itemData['teks'],
                    'urutan' => $index + 1,
                ];

                if (! empty($itemData['id'])) {
                    $existingItems->get($itemData['id'])->update($attributes);
                } else {
                    $templateWawancara->items()->create($attributes);
                }
            }
        });

        return redirect()->route('template-wawancara.index')
            ->with('success', 'Template wawancara berhasil diperbarui.');
    }

    public function destroy(InterviewTemplate $templateWawancara): RedirectResponse
    {
        Gate::authorize('delete', $templateWawancara);

        $templateWawancara->delete();

        return redirect()->route('template-wawancara.index')
            ->with('success', 'Template wawancara berhasil dihapus.');
    }
}
