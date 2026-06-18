<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Models\Unit;
use App\Models\Vacancy;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(): View
    {
        $q = trim((string) request('q', ''));
        $unitFilter = array_filter(array_map('intval', (array) request('unit', [])));
        $typeFilter = array_filter((array) request('type', []));

        $query = Vacancy::with('unit')->published()->whereNotNull('flyer_path');

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $query->where('judul_posisi', 'ilike', "%{$escaped}%");
        }

        if (! empty($unitFilter)) {
            $query->whereIn('unit_id', $unitFilter);
        }

        if (! empty($typeFilter)) {
            $query->whereIn('jenis_pekerjaan', $typeFilter);
        }

        $vacancies = $query->orderByDesc('created_at')->paginate(8)->withQueryString();

        $totalRoles = Vacancy::published()->whereNotNull('flyer_path')->count();

        $units = Unit::whereHas('vacancies', fn ($q) => $q->published()->whereNotNull('flyer_path'))
            ->withCount(['vacancies as published_count' => fn ($q) => $q->published()->whereNotNull('flyer_path')])
            ->orderBy('nama')
            ->get();

        $typeCounts = Vacancy::published()->whereNotNull('flyer_path')
            ->selectRaw('jenis_pekerjaan, count(*) as count')
            ->groupBy('jenis_pekerjaan')
            ->pluck('count', 'jenis_pekerjaan');

        $employmentTypes = EmploymentType::cases();

        return view('career.index', compact('vacancies', 'totalRoles', 'units', 'typeCounts', 'employmentTypes', 'unitFilter', 'typeFilter'));
    }

    public function show(Vacancy $vacancy): View
    {
        abort_unless(
            $vacancy->status === VacancyStatus::Published
            && $vacancy->tenggat_lamaran->gte(now()->startOfDay()),
            404,
        );

        $vacancy->load('unit', 'workflowTemplateSnapshot');

        return view('career.show', compact('vacancy'));
    }
}
