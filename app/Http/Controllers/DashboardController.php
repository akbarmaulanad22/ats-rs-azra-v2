<?php

namespace App\Http\Controllers;

use App\Actions\GetRecruitmentMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, GetRecruitmentMetrics $metrics): View
    {
        if (! auth()->user()->isHrAdmin()) {
            return view('dashboard');
        }

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'unit_id' => 'nullable|integer|exists:units,id',
            'vacancy_id' => 'nullable|integer|exists:vacancies,id',
        ]);

        return view('dashboard', [
            'isHrAdmin' => true,
            'filters' => $filters,
            ...$metrics->execute($filters),
        ]);
    }
}
