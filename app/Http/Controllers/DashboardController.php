<?php

namespace App\Http\Controllers;

use App\Actions\GetRecruitmentMetrics;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, GetRecruitmentMetrics $metrics): View
    {
        $user = auth()->user();

        $isUnitScope = $user->hasRole(Role::UnitHead, Role::Employee);
        $isOrgScope = $user->hasRole(Role::HrAdmin, Role::HrManager, Role::Director);

        if (! $isUnitScope && ! $isOrgScope) {
            return view('dashboard', ['scope' => 'none', 'hasUnit' => false]);
        }

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'unit_id' => 'nullable|integer|exists:units,id',
            'vacancy_id' => 'nullable|integer|exists:vacancies,id',
        ]);

        $lockedUnit = null;
        $hasUnit = true;

        if ($isUnitScope) {
            $unitId = $user->employee?->unit_id;
            $hasUnit = $unitId !== null;

            // Force the user's own unit. A unit-tier user must never widen scope, so any
            // request unit_id is overridden. When the user has no unit, scope to a
            // non-existent id (never null — null means "no filter" = org-wide data).
            $filters['unit_id'] = $unitId ?? 0;
            $lockedUnit = $user->employee?->unit;
        }

        return view('dashboard', [
            'scope' => $isUnitScope ? 'unit' : 'org',
            'lockedUnit' => $lockedUnit,
            'hasUnit' => $hasUnit,
            'filters' => $filters,
            ...$metrics->execute($filters),
        ]);
    }
}
