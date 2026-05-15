<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ApplicationPipelineController extends Controller
{
    public function __construct(private readonly ApplicationPipelineService $pipelineService) {}

    public function advance(Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('advance', $application);

        try {
            $this->pipelineService->advance($application);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['pipeline' => $e->getMessage()]);
        }

        return redirect()->route('lowongan.pipeline', $lowongan)->with('success', 'Kandidat berhasil dilanjutkan ke tahap berikutnya.');
    }

    public function fail(Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('fail', $application);

        try {
            $this->pipelineService->fail($application);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['pipeline' => $e->getMessage()]);
        }

        return redirect()->route('lowongan.pipeline', $lowongan)->with('success', 'Kandidat telah ditolak dari pipeline.');
    }
}
