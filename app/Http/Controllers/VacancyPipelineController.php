<?php

namespace App\Http\Controllers;

use App\Models\Vacancy;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VacancyPipelineController extends Controller
{
    public function show(Vacancy $lowongan): View
    {
        Gate::authorize('viewAny', Vacancy::class);

        $lowongan->load([
            'unit',
            'workflowTemplateSnapshot.stages',
            'applications.candidate',
            'applications.stages',
            'applications.testSubmission.snapshot',
        ]);

        $snapshotStages = $lowongan->workflowTemplateSnapshot->stages;

        $applicationsByStage = $snapshotStages->mapWithKeys(function ($stage) use ($lowongan) {
            $applicationsInStage = $lowongan->applications->filter(function ($application) use ($stage) {
                $currentStage = $application->currentStage();

                return $currentStage && $currentStage->key === $stage->key;
            });

            return [$stage->key => [
                'stage' => $stage,
                'applications' => $applicationsInStage->values(),
            ]];
        });

        return view('vacancies.pipeline', compact('lowongan', 'applicationsByStage'));
    }
}
