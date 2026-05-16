<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Application;
use App\Models\User;

class InterviewResultPolicy
{
    public function viewInterview(User $user, Application $application): bool
    {
        $application->loadMissing('vacancy.unit');

        return match ($user->role) {
            Role::UnitHead => $user->employee && $user->employee->unit === $application->vacancy->unit->nama,
            Role::HrManager, Role::Director => true,
            default => false,
        };
    }

    public function decideInterview(User $user, Application $application): bool
    {
        return $this->viewInterview($user, $application);
    }
}
