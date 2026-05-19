<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\InterviewTemplate;
use App\Models\User;

class InterviewTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function update(User $user, InterviewTemplate $interviewTemplate): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function delete(User $user, InterviewTemplate $interviewTemplate): bool
    {
        return $user->role === Role::HrAdmin;
    }
}
