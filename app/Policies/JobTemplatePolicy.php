<?php

namespace App\Policies;

use App\Models\JobTemplate;
use App\Models\User;

class JobTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isHrAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isHrAdmin();
    }

    public function update(User $user, JobTemplate $jobTemplate): bool
    {
        return $user->isHrAdmin();
    }

    public function delete(User $user, JobTemplate $jobTemplate): bool
    {
        return $user->isHrAdmin();
    }

    public function publish(User $user, JobTemplate $jobTemplate): bool
    {
        return $user->isHrAdmin();
    }

    public function manageTest(User $user, JobTemplate $jobTemplate): bool
    {
        return $user->isHrAdmin();
    }

    public function manageInterviewTemplates(User $user, JobTemplate $jobTemplate): bool
    {
        return $user->isHrAdmin();
    }
}
