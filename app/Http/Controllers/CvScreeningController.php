<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\CvScreeningDecisionRequest;
use App\Models\Application;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CvScreeningController extends Controller
{
    public function __construct(private readonly ApplicationPipelineService $pipelineService) {}

    public function decide(CvScreeningDecisionRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('decide', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $user = $request->user();
        $stageKey = $this->resolveStageKey($user->role);

        $application->load('stages');
        $screeningStage = $application->stages->firstWhere('key', $stageKey);

        abort_if(! $screeningStage, 404);

        if (! $screeningStage->status->isAdvanceable()) {
            return back()->withErrors(['screening' => 'Keputusan tidak dapat diberikan untuk tahap ini.']);
        }

        $catatan = $request->input('catatan');
        $keputusan = $request->input('keputusan');

        try {
            DB::transaction(function () use ($screeningStage, $catatan, $keputusan, $application, $user): void {
                $screeningStage->update(['catatan' => $catatan, 'reviewed_by' => $user->id]);

                match ($keputusan) {
                    'lulus' => $this->pipelineService->advance($application),
                    'gagal' => $this->pipelineService->fail($application),
                    'reserved' => $this->pipelineService->reserve($application),
                };
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['screening' => $e->getMessage()]);
        }

        $label = match ($keputusan) {
            'lulus' => 'diloloskan ke tahap berikutnya',
            'gagal' => 'ditolak dari pipeline',
            'reserved' => 'ditangguhkan',
        };

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', "Kandidat berhasil {$label}.");
    }

    private function resolveStageKey(Role $role): string
    {
        return in_array($role, [Role::UnitHead, Role::Employee], true) ? 'skrining_cv_user' : 'skrining_cv_hr';
    }
}
