<?php

namespace App\Services;

use App\Enums\ApplicationStageStatus;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Support\Collection;

class CallbackCandidateFinder
{
    /**
     * Stage keys treated as "CV screening". Failures at these stages are hidden
     * under the default (past-screening) filter.
     *
     * @var list<string>
     */
    public const SCREENING_STAGE_KEYS = ['skrining_cv_hr', 'skrining_cv_user'];

    /**
     * Build the callback list for a target Vacancy: one row per prior Gagal
     * application under the same JobTemplate, with per-candidate badges.
     *
     * Hard-excludes candidates already hired (completed onboarding) and
     * candidates who self-applied to the target Vacancy without an invite.
     *
     * @return Collection<int, array{application: Application, failed_stage_label: string, invited: bool, responded: bool, active_elsewhere: bool}>
     */
    public function forVacancy(Vacancy $vacancy, bool $includeScreening = false): Collection
    {
        $applications = Application::query()
            ->whereHas('vacancy', function ($query) use ($vacancy) {
                $query->where('job_template_id', $vacancy->job_template_id)
                    ->whereKeyNot($vacancy->id);
            })
            ->whereHas('stages', function ($query) use ($includeScreening) {
                $query->where('status', ApplicationStageStatus::Gagal);

                if (! $includeScreening) {
                    $query->whereNotIn('key', self::SCREENING_STAGE_KEYS);
                }
            })
            ->with(['candidate', 'stages', 'vacancy:id,judul_posisi'])
            ->get();

        $candidateIds = $applications->pluck('candidate_id')->unique();

        $invitedIds = $vacancy->callbackInvites()
            ->whereIn('candidate_id', $candidateIds)
            ->pluck('candidate_id')
            ->flip();

        $appliedToTargetIds = $vacancy->applications()
            ->whereIn('candidate_id', $candidateIds)
            ->pluck('candidate_id')
            ->flip();

        $hiredIds = Application::query()
            ->whereIn('candidate_id', $candidateIds)
            ->whereHas('onboardingResult')
            ->pluck('candidate_id')
            ->flip();

        // "Active elsewhere" = an in-progress application in a different Vacancy:
        // it has a live (Aktif/Reserved) stage and no Gagal stage. A failed
        // application is excluded by the Gagal guard even though its trailing
        // stages remain Pending.
        $activeElsewhereIds = Application::query()
            ->whereIn('candidate_id', $candidateIds)
            ->where('vacancy_id', '!=', $vacancy->id)
            ->whereHas('stages', function ($query) {
                $query->whereIn('status', [
                    ApplicationStageStatus::Aktif,
                    ApplicationStageStatus::Reserved,
                ]);
            })
            ->whereDoesntHave('stages', function ($query) {
                $query->where('status', ApplicationStageStatus::Gagal);
            })
            ->pluck('candidate_id')
            ->flip();

        return $applications
            ->reject(function (Application $application) use ($hiredIds, $invitedIds, $appliedToTargetIds) {
                $candidateId = $application->candidate_id;

                // Hard exclude: already hired (completed onboarding).
                if ($hiredIds->has($candidateId)) {
                    return true;
                }

                // Hard exclude: self-applied to the target Vacancy without an invite.
                return $appliedToTargetIds->has($candidateId) && ! $invitedIds->has($candidateId);
            })
            ->map(function (Application $application) use ($invitedIds, $appliedToTargetIds, $activeElsewhereIds) {
                $candidateId = $application->candidate_id;
                $failedStage = $application->stages
                    ->firstWhere('status', ApplicationStageStatus::Gagal);
                $invited = $invitedIds->has($candidateId);

                return [
                    'application' => $application,
                    'failed_stage_label' => $failedStage?->nama ?? '—',
                    'invited' => $invited,
                    'responded' => $invited && $appliedToTargetIds->has($candidateId),
                    'active_elsewhere' => $activeElsewhereIds->has($candidateId),
                ];
            })
            ->values();
    }
}
