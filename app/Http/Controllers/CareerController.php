<?php

namespace App\Http\Controllers;

use App\Enums\VacancyStatus;
use App\Models\Vacancy;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(): View
    {
        $vacancies = Vacancy::with('unit')
            ->published()
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('career.index', compact('vacancies'));
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
