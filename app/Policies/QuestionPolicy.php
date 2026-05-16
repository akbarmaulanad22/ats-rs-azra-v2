<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function update(User $user, Question $question): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function delete(User $user, Question $question): bool
    {
        return $user->role === Role::HrAdmin;
    }
}
