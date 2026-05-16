<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function advance(User $user, Application $application): bool
    {
        return $this->canManagePipeline($user);
    }

    public function fail(User $user, Application $application): bool
    {
        return $this->canManagePipeline($user);
    }

    public function viewScreeningDetail(User $user, Application $application): bool
    {
        if ($user->hasRole(Role::HrAdmin)) {
            return true;
        }

        if ($user->hasRole(Role::UnitHead)) {
            $employee = $user->employee;
            if (! $employee) {
                return false;
            }
            $application->loadMissing('vacancy.unit');

            return $employee->unit === $application->vacancy->unit->nama;
        }

        return false;
    }

    public function decide(User $user, Application $application): bool
    {
        return $this->viewScreeningDetail($user, $application);
    }

    private function canManagePipeline(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin, Role::HrManager, Role::UnitHead, Role::Director);
    }
}
