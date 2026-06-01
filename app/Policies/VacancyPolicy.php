<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\Vacancy;

class VacancyPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(Role::HrAdmin, Role::HrManager, Role::Director)) {
            return true;
        }

        if ($user->hasRole(Role::UnitHead, Role::Employee)) {
            return $user->employee !== null;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isHrAdmin();
    }

    public function update(User $user, Vacancy $vacancy): bool
    {
        return $user->isHrAdmin();
    }

    public function delete(User $user, Vacancy $vacancy): bool
    {
        return $user->isHrAdmin();
    }

    public function viewScreening(User $user, Vacancy $vacancy): bool
    {
        if ($user->hasRole(Role::HrAdmin)) {
            return true;
        }

        if ($user->hasRole(Role::UnitHead, Role::Employee)) {
            $employee = $user->employee;
            if (! $employee) {
                return false;
            }
            $vacancy->loadMissing('unit');

            return $employee->unit_id === $vacancy->unit_id;
        }

        return false;
    }

    public function viewInterview(User $user, Vacancy $vacancy): bool
    {
        if ($user->hasRole(Role::HrManager, Role::Director)) {
            return true;
        }

        if ($user->hasRole(Role::UnitHead)) {
            $employee = $user->employee;
            if (! $employee) {
                return false;
            }
            $vacancy->loadMissing('unit');

            return $employee->unit_id === $vacancy->unit_id;
        }

        return false;
    }

    public function viewCandidateDetail(User $user, Vacancy $vacancy): bool
    {
        if ($user->isHrAdmin()) {
            return true;
        }

        if ($user->hasRole(Role::HrManager, Role::Director)) {
            return true;
        }

        if ($user->hasRole(Role::UnitHead, Role::Employee)) {
            $employee = $user->employee;
            if (! $employee) {
                return false;
            }
            $vacancy->loadMissing('unit');

            return $employee->unit_id === $vacancy->unit_id;
        }

        return false;
    }

    public function manageInterviewTemplates(User $user, Vacancy $vacancy): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function export(User $user, Vacancy $vacancy): bool
    {
        return $user->isHrAdmin();
    }
}
