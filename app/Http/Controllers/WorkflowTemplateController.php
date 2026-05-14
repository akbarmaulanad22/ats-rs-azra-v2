<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkflowTemplateRequest;
use App\Http\Requests\UpdateWorkflowTemplateRequest;
use App\Models\WorkflowStage;
use App\Models\WorkflowTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WorkflowTemplateController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', WorkflowTemplate::class);

        $templates = WorkflowTemplate::withCount('stages')->orderBy('name')->get();

        return view('alur-rekrutmen.index', compact('templates'));
    }

    public function create(): View
    {
        Gate::authorize('create', WorkflowTemplate::class);

        $stages = WorkflowStage::orderBy('default_order')->get();

        return view('alur-rekrutmen.create', compact('stages'));
    }

    public function store(StoreWorkflowTemplateRequest $request): RedirectResponse
    {
        $template = WorkflowTemplate::create($request->only('name', 'description'));

        $sync = collect($request->stage_ids)
            ->mapWithKeys(fn ($id, $index) => [$id => ['position' => $index + 1]])
            ->all();

        $template->stages()->sync($sync);

        return redirect()
            ->route('alur-rekrutmen.index')
            ->with('status', 'Template alur rekrutmen "'.$template->name.'" berhasil dibuat.');
    }

    public function show(WorkflowTemplate $alur_rekrutmen): View
    {
        Gate::authorize('view', $alur_rekrutmen);

        $alur_rekrutmen->load(['stages' => fn ($q) => $q->orderByPivot('position')]);

        return view('alur-rekrutmen.show', ['template' => $alur_rekrutmen]);
    }

    public function edit(WorkflowTemplate $alur_rekrutmen): View
    {
        Gate::authorize('update', $alur_rekrutmen);

        $alur_rekrutmen->load(['stages' => fn ($q) => $q->orderByPivot('position')]);
        $allStages = WorkflowStage::orderBy('default_order')->get();

        return view('alur-rekrutmen.edit', ['template' => $alur_rekrutmen, 'stages' => $allStages]);
    }

    public function update(UpdateWorkflowTemplateRequest $request, WorkflowTemplate $alur_rekrutmen): RedirectResponse
    {
        $alur_rekrutmen->update($request->only('name', 'description'));

        $sync = collect($request->stage_ids)
            ->mapWithKeys(fn ($id, $index) => [$id => ['position' => $index + 1]])
            ->all();

        $alur_rekrutmen->stages()->sync($sync);

        return redirect()
            ->route('alur-rekrutmen.index')
            ->with('status', 'Template alur rekrutmen "'.$alur_rekrutmen->name.'" berhasil diperbarui.');
    }

    public function destroy(WorkflowTemplate $alur_rekrutmen): RedirectResponse
    {
        Gate::authorize('delete', $alur_rekrutmen);

        $name = $alur_rekrutmen->name;
        $alur_rekrutmen->delete();

        return redirect()
            ->route('alur-rekrutmen.index')
            ->with('status', 'Template alur rekrutmen "'.$name.'" berhasil dihapus.');
    }
}
