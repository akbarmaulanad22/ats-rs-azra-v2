<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use App\Models\Unit;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VacancyController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Vacancy::class);

        $vacancies = Vacancy::with(['unit', 'workflowTemplate'])
            ->when(
                $request->q,
                fn ($q, $search) => $q->whereRaw('LOWER(judul_posisi) LIKE ?', ['%'.strtolower($search).'%']),
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

        $units = Unit::orderBy('nama')->get();
        $templates = WorkflowTemplate::orderBy('nama')->get();
        $employmentTypes = EmploymentType::cases();
        $statuses = [VacancyStatus::Draft, VacancyStatus::Published];

        return view('vacancies.create', compact('units', 'templates', 'employmentTypes', 'statuses'));
    }

    public function store(StoreVacancyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? VacancyStatus::Draft->value;

        Vacancy::create($data);

        return redirect()->route('lowongan.index')
            ->with('status', 'Lowongan berhasil dibuat.');
    }

    public function edit(Vacancy $lowongan): View
    {
        Gate::authorize('update', $lowongan);

        $units = Unit::orderBy('nama')->get();
        $templates = WorkflowTemplate::orderBy('nama')->get();
        $employmentTypes = EmploymentType::cases();
        $statuses = VacancyStatus::cases();

        return view('vacancies.edit', compact('lowongan', 'units', 'templates', 'employmentTypes', 'statuses'));
    }

    public function update(UpdateVacancyRequest $request, Vacancy $lowongan): RedirectResponse
    {
        Gate::authorize('update', $lowongan);

        $lowongan->update($request->validated());

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
