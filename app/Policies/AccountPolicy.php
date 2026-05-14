<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function update(User $user, User $account): bool
    {
        return $user->hasRole(Role::HrAdmin) && $user->id !== $account->id;
    }

    public function delete(User $user, User $account): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }
}
