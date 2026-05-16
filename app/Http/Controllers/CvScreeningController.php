<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\CvScreeningDecisionRequest;
use App\Models\Application;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CvScreeningController extends Controller
{
    public function __construct(private readonly ApplicationPipelineService $pipelineService) {}

    public function index(Request $request, Vacancy $lowongan): View
    {
        Gate::authorize('viewScreening', $lowongan);

        $user = $request->user();
        $stageKey = $this->resolveStageKey($user->role);

        $statusFilter = $request->query('status');

        $applicationsQuery = Application::with(['candidate', 'stages'])
            ->where('vacancy_id', $lowongan->id)
            ->whereHas('stages', fn ($q) => $q->where('key', $stageKey));

        if ($statusFilter && in_array($statusFilter, ['aktif', 'reserved', 'selesai', 'gagal'])) {
            $applicationsQuery->whereHas('stages', fn ($q) => $q
                ->where('key', $stageKey)
                ->where('status', $statusFilter)
            );
        } else {
            // Exclude candidates that have not yet reached this stage (Pending)
            $applicationsQuery->whereHas('stages', fn ($q) => $q
                ->where('key', $stageKey)
                ->where('status', '!=', 'pending')
            );
        }

        $applications = $applicationsQuery->get()->map(function ($application) use ($stageKey) {
            $application->screening_stage = $application->stages->firstWhere('key', $stageKey);

            return $application;
        });

        return view('screening.index', compact('lowongan', 'applications', 'stageKey', 'statusFilter'));
    }

    public function show(Request $request, Vacancy $lowongan, Application $application): View
    {
        Gate::authorize('viewScreeningDetail', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $user = $request->user();
        $stageKey = $this->resolveStageKey($user->role);

        $application->load([
            'candidate.formalEducations',
            'candidate.informalEducations',
            'candidate.workExperiences',
            'candidate.organizationExperiences',
            'candidate.siblings',
            'candidate.spouses',
            'candidate.children',
            'candidate.languageSkills',
            'candidate.achievements',
            'stages',
        ]);

        $screeningStage = $application->stages->firstWhere('key', $stageKey);

        abort_if(! $screeningStage, 404);

        return view('screening.show', compact('lowongan', 'application', 'screeningStage'));
    }

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
            DB::transaction(function () use ($screeningStage, $catatan, $keputusan, $application): void {
                $screeningStage->update(['catatan' => $catatan]);

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
            ->route('lowongan.skrining.index', $lowongan)
            ->with('success', "Kandidat berhasil {$label}.");
    }

    private function resolveStageKey(Role $role): string
    {
        return $role === Role::UnitHead ? 'skrining_cv_kepala_unit' : 'skrining_cv_hr';
    }
}
