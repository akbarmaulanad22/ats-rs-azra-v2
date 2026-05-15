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

    private function canManagePipeline(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin, Role::HrManager, Role::UnitHead, Role::Director);
    }
}
