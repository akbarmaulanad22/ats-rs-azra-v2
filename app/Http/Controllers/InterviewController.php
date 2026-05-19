<?php

namespace App\Http\Controllers;

use App\Enums\InterviewTemplateType;
use App\Enums\Role;
use App\Http\Requests\StoreInterviewResultRequest;
use App\Models\Application;
use App\Models\InterviewResult;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InterviewController extends Controller
{
    public function __construct(private readonly ApplicationPipelineService $pipelineService) {}

    public function index(Request $request, Vacancy $lowongan): View
    {
        Gate::authorize('viewInterview', $lowongan);

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
            $applicationsQuery->whereHas('stages', fn ($q) => $q
                ->where('key', $stageKey)
                ->where('status', '!=', 'pending')
            );
        }

        $applications = $applicationsQuery->get()->map(function ($application) use ($stageKey) {
            $application->interview_stage = $application->stages->firstWhere('key', $stageKey);

            return $application;
        });

        return view('interview.index', compact('lowongan', 'applications', 'stageKey', 'statusFilter'));
    }

    public function show(Request $request, Vacancy $lowongan, Application $application): View
    {
        Gate::authorize('viewInterview', $application);

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
            'stages.interviewResult.ratings.interviewTemplate',
            'stages.interviewResult.readinessAnswers.interviewTemplate',
            'testSubmission.answers.question',
            'discSubmission.result',
        ]);

        $interviewStage = $application->stages->firstWhere('key', $stageKey);

        abort_if(! $interviewStage, 404);

        $existingResult = $interviewStage->interviewResult;

        $assignedTemplates = $lowongan->interviewTemplates()
            ->wherePivot('stage_key', $stageKey)
            ->where('tipe', InterviewTemplateType::KriteriaPenilaian)
            ->with('items')
            ->get();

        $assignedReadinessTemplates = $lowongan->interviewTemplates()
            ->wherePivot('stage_key', $stageKey)
            ->where('tipe', InterviewTemplateType::Kesiapan)
            ->with('items')
            ->get();

        $screeningStages = $application->stages->whereIn('key', ['skrining_cv_hr', 'skrining_cv_kepala_unit']);

        $priorInterviewStageKeys = match ($stageKey) {
            'wawancara_manajer_hr' => ['wawancara_kepala_unit'],
            'wawancara_direktur' => ['wawancara_kepala_unit', 'wawancara_manajer_hr'],
            default => [],
        };

        $priorInterviews = $application->stages
            ->whereIn('key', $priorInterviewStageKeys)
            ->filter(fn ($s) => $s->interviewResult !== null);

        return view('interview.show', compact(
            'lowongan',
            'application',
            'interviewStage',
            'existingResult',
            'assignedTemplates',
            'assignedReadinessTemplates',
            'screeningStages',
            'priorInterviews',
        ));
    }

    public function decide(StoreInterviewResultRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        Gate::authorize('decideInterview', $application);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $user = $request->user();
        $stageKey = $this->resolveStageKey($user->role);

        $application->load('stages');
        $interviewStage = $application->stages->firstWhere('key', $stageKey);

        abort_if(! $interviewStage, 404);

        if (! $interviewStage->status->isAdvanceable()) {
            return back()->withErrors(['interview' => 'Keputusan tidak dapat diberikan untuk tahap ini.']);
        }

        if ($interviewStage->interviewResult()->exists()) {
            return back()->withErrors(['interview' => 'Hasil wawancara sudah direkam sebelumnya.']);
        }

        $keputusan = $request->input('keputusan');
        $catatan = $request->input('catatan');
        $ratings = $request->input('ratings', []);
        $readinessAnswers = $request->input('readiness_answers', []);

        try {
            DB::transaction(function () use ($interviewStage, $application, $user, $keputusan, $catatan, $ratings, $readinessAnswers): void {
                $result = InterviewResult::create([
                    'application_id' => $application->id,
                    'application_stage_id' => $interviewStage->id,
                    'interviewer_id' => $user->id,
                    'keputusan' => $keputusan,
                    'catatan' => $catatan,
                    'submitted_at' => now(),
                ]);

                foreach ($ratings as $rating) {
                    $result->ratings()->create([
                        'interview_template_id' => $rating['interview_template_id'],
                        'nama_kriteria' => $rating['nama_kriteria'],
                        'nilai' => $rating['nilai'],
                    ]);
                }

                foreach ($readinessAnswers as $answer) {
                    $result->readinessAnswers()->create([
                        'interview_template_id' => $answer['interview_template_id'],
                        'pertanyaan' => $answer['pertanyaan'],
                        'jawaban' => $answer['jawaban'],
                    ]);
                }

                match ($keputusan) {
                    'lulus' => $this->pipelineService->advance($application),
                    'gagal' => $this->pipelineService->fail($application),
                    'reserved' => $this->pipelineService->reserve($application),
                };
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['interview' => $e->getMessage()]);
        }

        $label = match ($keputusan) {
            'lulus' => 'diloloskan ke tahap berikutnya',
            'gagal' => 'ditolak dari pipeline',
            'reserved' => 'ditangguhkan',
        };

        return redirect()
            ->route('lowongan.wawancara.index', $lowongan)
            ->with('success', "Kandidat berhasil {$label}.");
    }

    private function resolveStageKey(Role $role): string
    {
        return match ($role) {
            Role::UnitHead => 'wawancara_kepala_unit',
            Role::HrManager => 'wawancara_manajer_hr',
            Role::Director => 'wawancara_direktur',
            default => 'wawancara_kepala_unit',
        };
    }
}
