<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Candidate;
use App\Models\User;

class CandidatePolicy
{
    /**
     * Browse the talent pool (Kandidat Potensial). HR only.
     */
    public function viewTalentPool(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin, Role::HrManager);
    }

    /**
     * Flag a candidate into the talent pool. Anyone who manages a pipeline.
     */
    public function flagTalentPool(User $user, Candidate $candidate): bool
    {
        return $this->canManagePipeline($user);
    }

    /**
     * Remove a candidate from the talent pool. Anyone who manages a pipeline.
     */
    public function unflagTalentPool(User $user, Candidate $candidate): bool
    {
        return $this->canManagePipeline($user);
    }

    private function canManagePipeline(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin, Role::HrManager, Role::UnitHead, Role::Director);
    }
}
