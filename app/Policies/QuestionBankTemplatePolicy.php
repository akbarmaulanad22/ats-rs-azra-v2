<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\QuestionBankTemplate;
use App\Models\User;

class QuestionBankTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function update(User $user, QuestionBankTemplate $questionBankTemplate): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function delete(User $user, QuestionBankTemplate $questionBankTemplate): bool
    {
        return $user->role === Role::HrAdmin;
    }
}
