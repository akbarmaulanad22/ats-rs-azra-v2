<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\VacancyTest;

class VacancyTestPolicy
{
    public function manage(User $user, VacancyTest $vacancyTest): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function reviewEssay(User $user, VacancyTest $vacancyTest): bool
    {
        return $user->role === Role::HrAdmin;
    }
}
