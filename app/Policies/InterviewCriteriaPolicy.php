<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\InterviewCriteria;
use App\Models\User;

class InterviewCriteriaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function update(User $user, InterviewCriteria $interviewCriteria): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function delete(User $user, InterviewCriteria $interviewCriteria): bool
    {
        return $user->role === Role::HrAdmin;
    }
}
