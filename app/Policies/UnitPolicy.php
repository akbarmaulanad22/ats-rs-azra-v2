<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function view(User $user, Unit $unit): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function update(User $user, Unit $unit): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }
}
