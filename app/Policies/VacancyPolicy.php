<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\Vacancy;

class VacancyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isHrAdmin();
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

        if ($user->hasRole(Role::UnitHead)) {
            $employee = $user->employee;
            if (! $employee) {
                return false;
            }
            $vacancy->loadMissing('unit');

            return $employee->unit === $vacancy->unit->nama;
        }

        return false;
    }
}
