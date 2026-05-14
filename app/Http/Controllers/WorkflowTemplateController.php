<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkflowTemplateRequest;
use App\Http\Requests\UpdateWorkflowTemplateRequest;
use App\Models\Stage;
use App\Models\WorkflowTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WorkflowTemplateController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', WorkflowTemplate::class);

        $templates = WorkflowTemplate::with('stages')->orderBy('nama')->get();

        return view('workflow-templates.index', compact('templates'));
    }

    public function create(): View
    {
        Gate::authorize('create', WorkflowTemplate::class);

        $stages = Stage::orderByDesc('is_locked_first')->orderBy('is_locked_last')->orderBy('nama')->get();

        return view('workflow-templates.create', compact('stages'));
    }

    public function store(StoreWorkflowTemplateRequest $request): RedirectResponse
    {
        $stageIds = $request->validated()['stages'];

        $this->validateStageConstraints($stageIds);

        $template = WorkflowTemplate::create(['nama' => $request->validated()['nama']]);

        $pivot = [];
        foreach ($stageIds as $position => $stageId) {
            $pivot[(int) $stageId] = ['position' => $position + 1];
        }
        $template->stages()->sync($pivot);

        return redirect()->route('template-alur.index')
            ->with('status', 'Template alur kerja berhasil dibuat.');
    }

    public function edit(WorkflowTemplate $templateAlur): View
    {
        Gate::authorize('update', $templateAlur);

        $stages = Stage::orderByDesc('is_locked_first')->orderBy('is_locked_last')->orderBy('nama')->get();

        return view('workflow-templates.edit', compact('templateAlur', 'stages'));
    }

    public function update(UpdateWorkflowTemplateRequest $request, WorkflowTemplate $templateAlur): RedirectResponse
    {
        Gate::authorize('update', $templateAlur);

        $stageIds = $request->validated()['stages'];

        $this->validateStageConstraints($stageIds);

        $templateAlur->update(['nama' => $request->validated()['nama']]);

        $pivot = [];
        foreach ($stageIds as $position => $stageId) {
            $pivot[(int) $stageId] = ['position' => $position + 1];
        }
        $templateAlur->stages()->sync($pivot);

        return redirect()->route('template-alur.index')
            ->with('status', 'Template alur kerja berhasil diperbarui.');
    }

    public function destroy(WorkflowTemplate $templateAlur): RedirectResponse
    {
        Gate::authorize('delete', $templateAlur);

        if ($templateAlur->vacancies()->exists()) {
            return redirect()->route('template-alur.index')
                ->with('error', 'Template tidak dapat dihapus karena sedang digunakan oleh lowongan aktif.');
        }

        $templateAlur->delete();

        return redirect()->route('template-alur.index')
            ->with('status', 'Template alur kerja berhasil dihapus.');
    }

    private function validateStageConstraints(array $stageIds): void
    {
        $stages = Stage::whereIn('id', $stageIds)->get()->keyBy('id');

        $firstId = (int) $stageIds[0];
        $lastId = (int) $stageIds[array_key_last($stageIds)];

        $firstStage = $stages[$firstId] ?? null;
        $lastStage = $stages[$lastId] ?? null;

        if (! $firstStage || ! $firstStage->is_locked_first) {
            abort(422, 'Tahap pertama harus berupa "Aplikasi".');
        }

        if (! $lastStage || ! $lastStage->is_locked_last) {
            abort(422, 'Tahap terakhir harus berupa "Onboarding".');
        }

        foreach ($stages as $stage) {
            if ($stage->is_locked_first && $firstId !== $stage->id) {
                abort(422, 'Tahap "Aplikasi" harus selalu berada di posisi pertama.');
            }
            if ($stage->is_locked_last && $lastId !== $stage->id) {
                abort(422, 'Tahap "Onboarding" harus selalu berada di posisi terakhir.');
            }
        }
    }
}
