<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\WorkflowTemplate;

class WorkflowTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function view(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function update(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function delete(User $user, WorkflowTemplate $workflowTemplate): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }
}
