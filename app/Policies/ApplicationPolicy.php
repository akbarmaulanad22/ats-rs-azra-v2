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

        if ($user->hasRole(Role::UnitHead, Role::Employee)) {
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

    public function viewInterview(User $user, Application $application): bool
    {
        $application->loadMissing('vacancy.unit');

        return match ($user->role) {
            Role::UnitHead, Role::Employee => $user->employee && $user->employee->unit === $application->vacancy->unit->nama,
            Role::HrManager, Role::Director => true,
            default => false,
        };
    }

    public function decideInterview(User $user, Application $application): bool
    {
        return $this->viewInterview($user, $application);
    }

    public function manageOffering(User $user, Application $application): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function manageMcu(User $user, Application $application): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function manageOnboarding(User $user, Application $application): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function scheduleInterview(User $user, Application $application): bool
    {
        return $user->hasRole(Role::HrAdmin, Role::HrManager);
    }

    private function canManagePipeline(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin, Role::HrManager, Role::UnitHead, Role::Director);
    }
}
