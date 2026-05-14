<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vacancy;

class VacancyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isHrAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isHrAdmin();
    }

    public function update(User $user, Vacancy $vacancy): bool
    {
        return $user->isHrAdmin();
    }

    public function delete(User $user, Vacancy $vacancy): bool
    {
        return $user->isHrAdmin();
    }
}
