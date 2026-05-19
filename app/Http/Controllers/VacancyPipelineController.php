<?php

namespace App\Http\Controllers;

use App\Enums\InterviewTemplateType;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VacancyPipelineController extends Controller
{
    public function index(Request $request, Vacancy $lowongan): View
    {
        Gate::authorize('viewCandidateDetail', $lowongan);

        $lowongan->load(['unit', 'workflowTemplateSnapshot.stages']);

        $snapshotStages = $lowongan->workflowTemplateSnapshot->stages->sortBy('position')->values();

        $query = Application::with(['candidate', 'stages'])
            ->where('vacancy_id', $lowongan->id);

        if ($search = $request->query('search')) {
            $query->whereHas('candidate', fn ($q) => $q->where('nama_lengkap', 'ilike', "%{$search}%"));
        }

        if ($stageFilter = $request->query('stage')) {
            $query->whereHas('stages', fn ($q) => $q
                ->where('key', $stageFilter)
                ->where('status', '!=', 'pending')
            );
        }

        $statusFilter = $request->query('status');
        if ($statusFilter === 'gagal') {
            $query->whereHas('stages', fn ($q) => $q->where('status', 'gagal'));
        } elseif ($statusFilter === 'aktif') {
            $query->whereHas('stages', fn ($q) => $q->where('status', 'aktif'));
        } elseif ($statusFilter === 'ditangguhkan') {
            $query->whereHas('stages', fn ($q) => $q->where('status', 'reserved'));
        } elseif ($statusFilter === 'selesai') {
            $query->whereDoesntHave('stages', fn ($q) => $q->whereIn('status', ['pending', 'aktif', 'reserved', 'gagal']));
        } elseif ($statusFilter === 'menunggu') {
            $query->whereDoesntHave('stages', fn ($q) => $q->whereIn('status', ['aktif', 'reserved', 'selesai', 'gagal']));
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('vacancies.pipeline', compact('lowongan', 'applications', 'snapshotStages', 'stageFilter', 'statusFilter'));
    }

    public function showApplication(Request $request, Vacancy $lowongan, Application $application): View
    {
        Gate::authorize('viewCandidateDetail', $lowongan);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $lowongan->load(['unit', 'workflowTemplateSnapshot.stages']);

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
            'candidate.applications.vacancy.unit',
            'stages.interviewResult.ratings.interviewTemplate',
            'stages.interviewResult.readinessAnswers.interviewTemplate',
            'testSubmission.answers.question.options',
            'testSubmission.answers.selectedOption',
            'testSubmission.snapshot',
            'discSubmission.result',
            'mbtiSubmission.result',
            'offeringLetter',
            'mcuResult',
            'onboardingResult',
        ]);

        $snapshotStages = $lowongan->workflowTemplateSnapshot->stages->sortBy('position')->values();
        $currentStage = $application->currentStage();

        $assignedTemplates = collect();
        $assignedReadinessTemplates = collect();
        $interviewStageKeys = ['wawancara_kepala_unit', 'wawancara_manajer_hr', 'wawancara_direktur'];

        if ($currentStage && in_array($currentStage->key, $interviewStageKeys, true)) {
            $assignedTemplates = $lowongan->interviewTemplates()
                ->wherePivot('stage_key', $currentStage->key)
                ->where('tipe', InterviewTemplateType::KriteriaPenilaian)
                ->with('items')
                ->get();

            $assignedReadinessTemplates = $lowongan->interviewTemplates()
                ->wherePivot('stage_key', $currentStage->key)
                ->where('tipe', InterviewTemplateType::Kesiapan)
                ->with('items')
                ->get();
        }

        $priorInterviewStageKeys = [];
        if ($currentStage) {
            $priorInterviewStageKeys = match ($currentStage->key) {
                'wawancara_manajer_hr' => ['wawancara_kepala_unit'],
                'wawancara_direktur' => ['wawancara_kepala_unit', 'wawancara_manajer_hr'],
                default => [],
            };
        }

        $priorInterviews = $application->stages
            ->whereIn('key', $priorInterviewStageKeys)
            ->filter(fn ($s) => $s->interviewResult !== null);

        $testAllReviewed = false;
        if ($application->testSubmission?->submitted_at) {
            $testAllReviewed = $application->testSubmission->answers->every(fn ($a) => $a->is_reviewed);
        }

        return view('vacancies.pipeline-show', compact(
            'lowongan',
            'application',
            'snapshotStages',
            'currentStage',
            'assignedTemplates',
            'assignedReadinessTemplates',
            'priorInterviews',
            'testAllReviewed',
        ));
    }
}
