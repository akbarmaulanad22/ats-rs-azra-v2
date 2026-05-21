<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Enums\Role;
use App\Enums\VacancyStatus;
use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use App\Models\Unit;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VacancyController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Vacancy::class);

        $user = auth()->user();
        $query = Vacancy::with(['unit', 'workflowTemplateSnapshot']);

        if ($user->hasRole(Role::UnitHead)) {
            $employee = $user->employee;
            $unit = $employee?->unit;

            if (! $unit) {
                session()->flash('warning', 'Unit Anda tidak ditemukan. Hubungi Admin HR untuk memperbaiki data.');
            }

            $query->where('unit_id', $unit?->id ?? 0);
        }

        $vacancies = $query
            ->when(
                $request->q,
                fn ($q, $search) => $q->whereRaw('LOWER(judul_posisi) LIKE ?', ['%'.strtolower(str_replace(['%', '_'], ['\%', '\_'], $search)).'%']),
            )
            ->when(
                $request->status,
                fn ($q, $status) => $q->where('status', $status),
            )
            ->when(
                $request->unit_id,
                fn ($q, $unitId) => $q->where('unit_id', $unitId),
            )
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $units = Unit::orderBy('nama')->get();

        return view('vacancies.index', compact('vacancies', 'units'));
    }

    public function create(): View
    {
        Gate::authorize('create', Vacancy::class);

        $employmentTypes = EmploymentType::cases();
        $statuses = [VacancyStatus::Draft, VacancyStatus::Published];

        return view('vacancies.create', compact('employmentTypes', 'statuses'));
    }

    public function store(StoreVacancyRequest $request): RedirectResponse
    {
        Gate::authorize('create', Vacancy::class);

        $template = WorkflowTemplate::with('stages')->findOrFail($request->validated('workflow_template_id'));

        $status = $request->validated('status') ?? VacancyStatus::Draft->value;
        $hasTestStage = $template->stages->contains('key', 'tes_kompetensi');

        if ($status === VacancyStatus::Published->value && $hasTestStage) {
            return back()->withInput()->withErrors([
                'status' => 'Lowongan tidak dapat dipublikasikan sebelum tes kompetensi dikonfigurasi. Simpan sebagai draft terlebih dahulu, lalu konfigurasi tes.',
            ]);
        }

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $data = collect($request->validated())->except('workflow_template_id')->all();
        $data['workflow_template_snapshot_id'] = $snapshot->id;

        $vacancy = Vacancy::create($data);

        return redirect()->route('lowongan.index')
            ->with('status', 'Lowongan berhasil dibuat.');
    }

    public function edit(Vacancy $lowongan): View
    {
        Gate::authorize('update', $lowongan);

        $employmentTypes = EmploymentType::cases();
        $statuses = VacancyStatus::cases();

        $lowongan->load('unit', 'workflowTemplateSnapshot.workflowTemplate');

        return view('vacancies.edit', compact('lowongan', 'employmentTypes', 'statuses'));
    }

    public function update(UpdateVacancyRequest $request, Vacancy $lowongan): RedirectResponse
    {
        Gate::authorize('update', $lowongan);

        $data = $request->validated();

        if (isset($data['workflow_template_id'])) {
            $template = WorkflowTemplate::with('stages')->findOrFail($data['workflow_template_id']);
            $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);
            unset($data['workflow_template_id']);
            $data['workflow_template_snapshot_id'] = $snapshot->id;
        }

        $status = $data['status'] ?? $lowongan->status->value;
        $snapshotStages = $lowongan->workflowTemplateSnapshot->stages;
        $hasTestStage = $snapshotStages->contains('key', 'tes_kompetensi');

        if ($status === VacancyStatus::Published->value && $hasTestStage && ! $lowongan->vacancyTest?->latestSnapshot) {
            return back()->withInput()->withErrors([
                'status' => 'Lowongan tidak dapat dipublikasikan sebelum tes kompetensi dikonfigurasi.',
            ]);
        }

        $lowongan->update($data);

        return redirect()->route('lowongan.index')
            ->with('status', 'Lowongan berhasil diperbarui.');
    }

    public function destroy(Vacancy $lowongan): RedirectResponse
    {
        Gate::authorize('delete', $lowongan);

        $lowongan->delete();

        return redirect()->route('lowongan.index')
            ->with('status', 'Lowongan berhasil dihapus.');
    }
}
