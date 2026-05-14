<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->hasRole(Role::HrAdmin)) {
            return true;
        }

        return $user->hasRole(Role::Employee) && $employee->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }
}
