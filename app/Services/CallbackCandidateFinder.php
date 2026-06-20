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
        $applications = $this->gagalApplications($vacancy, $includeScreening);
        $candidateIds = $applications->pluck('candidate_id')->unique();

        $invitedIds = $this->invitedIds($vacancy, $candidateIds);
        $appliedToTargetIds = $this->appliedToTargetIds($vacancy, $candidateIds);
        $activeElsewhereIds = $this->activeElsewhereIds($vacancy, $candidateIds);

        return $this->rejectIneligible($applications, $candidateIds, $invitedIds, $appliedToTargetIds)
            ->map(function (Application $application) use ($invitedIds, $appliedToTargetIds, $activeElsewhereIds) {
                $candidateId = $application->candidate_id;
                $failedStage = $application->stages
                    ->firstWhere('status', ApplicationStageStatus::Gagal);

                return [
                    'application' => $application,
                    'failed_stage_label' => $failedStage?->nama ?? '—',
                    'invited' => $invitedIds->has($candidateId),
                    'responded' => $appliedToTargetIds->has($candidateId),
                    'active_elsewhere' => $activeElsewhereIds->has($candidateId),
                ];
            })
            ->values();
    }

    /**
     * Eligible candidate ids for the write path. Uses the widest set
     * (includeScreening): the screening filter is a view toggle, not an
     * eligibility rule, so screening-stage failures stay invitable.
     *
     * @return list<int>
     */
    public function eligibleCandidateIds(Vacancy $vacancy): array
    {
        $applications = $this->gagalApplications($vacancy, includeScreening: true);
        $candidateIds = $applications->pluck('candidate_id')->unique();

        $invitedIds = $this->invitedIds($vacancy, $candidateIds);
        $appliedToTargetIds = $this->appliedToTargetIds($vacancy, $candidateIds);

        return $this->rejectIneligible($applications, $candidateIds, $invitedIds, $appliedToTargetIds)
            ->pluck('candidate_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Prior Gagal applications under the same JobTemplate (excluding the target
     * Vacancy itself), eager-loaded for badge/label rendering.
     *
     * @return Collection<int, Application>
     */
    private function gagalApplications(Vacancy $vacancy, bool $includeScreening): Collection
    {
        return Application::query()
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
    }

    /**
     * Drop candidates who are already hired or who self-applied to the target
     * Vacancy without an invite.
     *
     * @param  Collection<int, Application>  $applications
     * @param  Collection<int, int>  $candidateIds
     * @param  Collection<int, int>  $invitedIds  candidate_id => index
     * @param  Collection<int, int>  $appliedToTargetIds  candidate_id => index
     * @return Collection<int, Application>
     */
    private function rejectIneligible(
        Collection $applications,
        Collection $candidateIds,
        Collection $invitedIds,
        Collection $appliedToTargetIds,
    ): Collection {
        $hiredIds = $this->hiredIds($candidateIds);

        return $applications->reject(function (Application $application) use ($hiredIds, $invitedIds, $appliedToTargetIds) {
            $candidateId = $application->candidate_id;

            // Hard exclude: already hired (completed onboarding).
            if ($hiredIds->has($candidateId)) {
                return true;
            }

            // Hard exclude: self-applied to the target Vacancy without an invite.
            return $appliedToTargetIds->has($candidateId) && ! $invitedIds->has($candidateId);
        });
    }

    /**
     * @param  Collection<int, int>  $candidateIds
     * @return Collection<int, int> candidate_id => index
     */
    private function invitedIds(Vacancy $vacancy, Collection $candidateIds): Collection
    {
        return $vacancy->callbackInvites()
            ->whereIn('candidate_id', $candidateIds)
            ->pluck('candidate_id')
            ->flip();
    }

    /**
     * @param  Collection<int, int>  $candidateIds
     * @return Collection<int, int> candidate_id => index
     */
    private function appliedToTargetIds(Vacancy $vacancy, Collection $candidateIds): Collection
    {
        return $vacancy->applications()
            ->whereIn('candidate_id', $candidateIds)
            ->pluck('candidate_id')
            ->flip();
    }

    /**
     * @param  Collection<int, int>  $candidateIds
     * @return Collection<int, int> candidate_id => index
     */
    private function hiredIds(Collection $candidateIds): Collection
    {
        return Application::query()
            ->whereIn('candidate_id', $candidateIds)
            ->whereHas('onboardingResult')
            ->pluck('candidate_id')
            ->flip();
    }

    /**
     * "Active elsewhere" = an in-progress application in a different Vacancy: it
     * has a live (Aktif/Reserved) stage and no Gagal stage. A failed application
     * is excluded by the Gagal guard even though its trailing stages remain Pending.
     *
     * @param  Collection<int, int>  $candidateIds
     * @return Collection<int, int> candidate_id => index
     */
    private function activeElsewhereIds(Vacancy $vacancy, Collection $candidateIds): Collection
    {
        return Application::query()
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
    }
}
