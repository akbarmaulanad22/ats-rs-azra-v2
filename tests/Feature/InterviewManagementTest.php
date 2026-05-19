<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\InterviewTemplateType;
use App\Enums\Role;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\Employee;
use App\Models\InterviewResult;
use App\Models\InterviewTemplate;
use App\Models\InterviewTemplateItem;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyInterviewTemplate;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewManagementTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    private function createVacancy(Unit $unit, array $stageKeys = ['lamaran', 'skrining_cv_hr', 'wawancara_kepala_unit', 'wawancara_manajer_hr', 'wawancara_direktur', 'onboarding']): Vacancy
    {
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $vacancy = Vacancy::factory()->published()->create([
            'unit_id' => $unit->id,
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);

        return $vacancy;
    }

    /** @param array<int, ApplicationStageStatus> $stageStatuses */
    private function makeApplication(Vacancy $vacancy, array $stageStatuses = []): Application
    {
        $vacancy->load('workflowTemplateSnapshot.stages');

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => Candidate::factory()->create()->id,
        ]);

        $snapshotStages = $vacancy->workflowTemplateSnapshot->stages->sortBy('position')->values();

        foreach ($snapshotStages as $index => $stage) {
            $status = $stageStatuses[$index] ?? ($index === 0
                ? ApplicationStageStatus::Selesai
                : ($index === 1 ? ApplicationStageStatus::Aktif : ApplicationStageStatus::Pending));

            ApplicationStage::factory()->create([
                'application_id' => $application->id,
                'position' => $stage->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $status,
            ]);
        }

        return $application;
    }

    private function makeAtInterviewStage(Vacancy $vacancy, string $stageKey): Application
    {
        $vacancy->load('workflowTemplateSnapshot.stages');
        $snapshotStages = $vacancy->workflowTemplateSnapshot->stages->sortBy('position')->values();

        $targetPosition = $snapshotStages->search(fn ($s) => $s->key === $stageKey);

        $statuses = $snapshotStages->map(function ($stage, $index) use ($targetPosition) {
            if ($index < $targetPosition) {
                return ApplicationStageStatus::Selesai;
            }
            if ($index === $targetPosition) {
                return ApplicationStageStatus::Aktif;
            }

            return ApplicationStageStatus::Pending;
        })->toArray();

        return $this->makeApplication($vacancy, $statuses);
    }

    private function makeUnitHead(Unit $unit): User
    {
        $user = User::factory()->withRole(Role::UnitHead)->create();
        Employee::factory()->create([
            'user_id' => $user->id,
            'unit' => $unit->nama,
        ]);

        return $user;
    }

    private function makeHrManager(): User
    {
        return User::factory()->withRole(Role::HrManager)->create();
    }

    private function makeDirector(): User
    {
        return User::factory()->withRole(Role::Director)->create();
    }

    /** Assigns a kesiapan template with items to the given stage keys on a vacancy. */
    private function assignReadinessTemplate(Vacancy $vacancy, array $stageKeys, string $templateNama = 'Kesiapan Umum'): InterviewTemplate
    {
        $template = InterviewTemplate::factory()->kesiapan()->create(['nama' => $templateNama]);
        InterviewTemplateItem::factory()->count(2)->create(['interview_template_id' => $template->id]);

        foreach ($stageKeys as $stageKey) {
            VacancyInterviewTemplate::create([
                'vacancy_id' => $vacancy->id,
                'interview_template_id' => $template->id,
                'stage_key' => $stageKey,
            ]);
        }

        return $template;
    }

    /** Builds readiness_answers payload for all kesiapan templates assigned to a stage. */
    private function readinessAnswersFor(Vacancy $vacancy, string $stageKey, bool $jawaban = true): array
    {
        $templates = $vacancy->interviewTemplates()
            ->wherePivot('stage_key', $stageKey)
            ->where('tipe', InterviewTemplateType::Kesiapan)
            ->with('items')
            ->get();

        $answers = [];
        foreach ($templates as $template) {
            foreach ($template->items as $item) {
                $answers[] = [
                    'interview_template_id' => $template->id,
                    'pertanyaan' => $item->teks,
                    'jawaban' => $jawaban ? '1' : '0',
                ];
            }
        }

        return $answers;
    }

    /** Assigns a kriteria_penilaian template with items to the given stage keys on a vacancy. */
    private function assignCriteriaTemplate(Vacancy $vacancy, array $stageKeys, string $templateNama = 'Kriteria Umum'): InterviewTemplate
    {
        $template = InterviewTemplate::factory()->kriteriaPenilaian()->create(['nama' => $templateNama]);
        InterviewTemplateItem::factory()->count(3)->create(['interview_template_id' => $template->id]);

        foreach ($stageKeys as $stageKey) {
            VacancyInterviewTemplate::create([
                'vacancy_id' => $vacancy->id,
                'interview_template_id' => $template->id,
                'stage_key' => $stageKey,
            ]);
        }

        return $template;
    }

    private function criteriaRatingsFor(Vacancy $vacancy, string $stageKey): array
    {
        $templates = $vacancy->interviewTemplates()
            ->wherePivot('stage_key', $stageKey)
            ->where('tipe', InterviewTemplateType::KriteriaPenilaian)
            ->with('items')
            ->get();

        $ratings = [];
        foreach ($templates as $template) {
            foreach ($template->items as $item) {
                $ratings[] = [
                    'interview_template_id' => $template->id,
                    'nama_kriteria' => $item->teks,
                    'nilai' => 4,
                ];
            }
        }

        return $ratings;
    }

    // ── Per-Vacancy Template Assignment ──────────────────────────────────────

    public function test_hr_admin_can_view_vacancy_criteria_page(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);

        $response = $this->actingAs($admin)->get(route('lowongan.template-wawancara.show', $vacancy));

        $response->assertOk();
        $response->assertViewIs('vacancy-interview-templates.show');
    }

    public function test_hr_admin_can_save_vacancy_template_assignments(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);

        $templateA = InterviewTemplate::factory()->kriteriaPenilaian()->create();
        $templateB = InterviewTemplate::factory()->kesiapan()->create();

        $response = $this->actingAs($admin)->post(route('lowongan.template-wawancara.save', $vacancy), [
            'assignments' => [
                'wawancara_kepala_unit' => [$templateA->id, $templateB->id],
                'wawancara_manajer_hr' => [$templateA->id],
            ],
        ]);

        $response->assertRedirect(route('lowongan.template-wawancara.show', $vacancy));
        $this->assertDatabaseHas('vacancy_interview_templates', [
            'vacancy_id' => $vacancy->id,
            'interview_template_id' => $templateA->id,
            'stage_key' => 'wawancara_kepala_unit',
        ]);
        $this->assertDatabaseHas('vacancy_interview_templates', [
            'vacancy_id' => $vacancy->id,
            'interview_template_id' => $templateB->id,
            'stage_key' => 'wawancara_kepala_unit',
        ]);
        $this->assertDatabaseHas('vacancy_interview_templates', [
            'vacancy_id' => $vacancy->id,
            'interview_template_id' => $templateA->id,
            'stage_key' => 'wawancara_manajer_hr',
        ]);
    }

    public function test_save_with_empty_assignments_clears_all(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);

        $template = InterviewTemplate::factory()->create();
        VacancyInterviewTemplate::create([
            'vacancy_id' => $vacancy->id,
            'interview_template_id' => $template->id,
            'stage_key' => 'wawancara_kepala_unit',
        ]);

        $response = $this->actingAs($admin)->post(route('lowongan.template-wawancara.save', $vacancy), [
            'assignments' => [],
        ]);

        $response->assertRedirect(route('lowongan.template-wawancara.show', $vacancy));
        $this->assertDatabaseMissing('vacancy_interview_templates', [
            'vacancy_id' => $vacancy->id,
        ]);
    }

    public function test_save_ignores_invalid_stage_key(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);

        $template = InterviewTemplate::factory()->create();

        $response = $this->actingAs($admin)->post(route('lowongan.template-wawancara.save', $vacancy), [
            'assignments' => [
                'invalid_stage' => [$template->id],
                'wawancara_kepala_unit' => [$template->id],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('vacancy_interview_templates', [
            'vacancy_id' => $vacancy->id,
            'stage_key' => 'invalid_stage',
        ]);
        $this->assertDatabaseHas('vacancy_interview_templates', [
            'vacancy_id' => $vacancy->id,
            'stage_key' => 'wawancara_kepala_unit',
        ]);
    }

    public function test_save_deduplicates_same_template_in_stage(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);

        $template = InterviewTemplate::factory()->create();

        $response = $this->actingAs($admin)->post(route('lowongan.template-wawancara.save', $vacancy), [
            'assignments' => [
                'wawancara_kepala_unit' => [$template->id, $template->id],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, VacancyInterviewTemplate::where([
            'vacancy_id' => $vacancy->id,
            'interview_template_id' => $template->id,
            'stage_key' => 'wawancara_kepala_unit',
        ])->count());
    }

    public function test_non_admin_cannot_manage_vacancy_criteria(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);

        $response = $this->actingAs($unitHead)->get(route('lowongan.template-wawancara.show', $vacancy));

        $response->assertForbidden();
    }

    // ── Interview Dashboard ───────────────────────────────────────────────────

    public function test_unit_head_can_view_interview_dashboard_for_own_unit(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->get(route('lowongan.wawancara.index', $vacancy));

        $response->assertOk();
        $response->assertViewIs('interview.index');
    }

    public function test_unit_head_cannot_view_interview_dashboard_for_other_unit(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $otherUnit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($otherUnit);

        $response = $this->actingAs($unitHead)->get(route('lowongan.wawancara.index', $vacancy));

        $response->assertForbidden();
    }

    public function test_hr_manager_can_view_interview_dashboard(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $hrManager = $this->makeHrManager();
        $vacancy = $this->createVacancy($unit);

        $response = $this->actingAs($hrManager)->get(route('lowongan.wawancara.index', $vacancy));

        $response->assertOk();
    }

    public function test_director_can_view_interview_dashboard(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $director = $this->makeDirector();
        $vacancy = $this->createVacancy($unit);

        $response = $this->actingAs($director)->get(route('lowongan.wawancara.index', $vacancy));

        $response->assertOk();
    }

    public function test_hr_admin_cannot_view_interview_dashboard(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);

        $response = $this->actingAs($admin)->get(route('lowongan.wawancara.index', $vacancy));

        $response->assertForbidden();
    }

    public function test_dashboard_shows_candidates_at_correct_interview_stage(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $hrManager = $this->makeHrManager();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_manajer_hr');

        $response = $this->actingAs($hrManager)->get(route('lowongan.wawancara.index', $vacancy));

        $response->assertOk();
        $response->assertSee($application->candidate->nama_lengkap);
    }

    public function test_dashboard_hides_candidates_not_yet_at_stage(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $hrManager = $this->makeHrManager();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($hrManager)->get(route('lowongan.wawancara.index', $vacancy));

        $response->assertOk();
        $response->assertDontSee($application->candidate->nama_lengkap);
    }

    // ── Interview Show (Candidate Profile) ────────────────────────────────────

    public function test_unit_head_can_view_candidate_interview_form(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->get(route('lowongan.wawancara.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertViewIs('interview.show');
        $response->assertSee($application->candidate->nama_lengkap);
    }

    public function test_unit_head_cannot_view_interview_form_for_other_unit(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $otherUnit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($otherUnit);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->get(route('lowongan.wawancara.show', [$vacancy, $application]));

        $response->assertForbidden();
    }

    public function test_interview_form_shows_blocking_message_when_no_templates_assigned(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->get(route('lowongan.wawancara.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertSee('Belum ada kriteria, hubungi HR Admin.');
    }

    public function test_interview_form_shows_criteria_grouped_by_template(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $template = $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit'], 'Kriteria Umum');

        $response = $this->actingAs($unitHead)->get(route('lowongan.wawancara.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertSee('Kriteria Umum');
        $response->assertSee($template->items->first()->teks);
    }

    // ── Record Interview Result: Pass ─────────────────────────────────────────

    public function test_unit_head_can_pass_candidate_at_unit_head_interview(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'catatan' => 'Kandidat sangat baik.', 'ratings' => $ratings]
        );

        $response->assertRedirect(route('lowongan.wawancara.index', $vacancy));

        $application->load('stages');
        $interviewStage = $application->stages->firstWhere('key', 'wawancara_kepala_unit');
        $this->assertEquals(ApplicationStageStatus::Selesai, $interviewStage->status);

        $this->assertDatabaseHas('interview_results', [
            'application_id' => $application->id,
            'application_stage_id' => $interviewStage->id,
            'interviewer_id' => $unitHead->id,
            'keputusan' => 'lulus',
            'catatan' => 'Kandidat sangat baik.',
        ]);

        $nextStage = $application->stages->firstWhere('key', 'wawancara_manajer_hr');
        $this->assertEquals(ApplicationStageStatus::Aktif, $nextStage->status);
    }

    public function test_hr_manager_can_pass_candidate_at_hr_manager_interview(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $hrManager = $this->makeHrManager();
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_manajer_hr']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_manajer_hr');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_manajer_hr');

        $response = $this->actingAs($hrManager)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings]
        );

        $response->assertRedirect(route('lowongan.wawancara.index', $vacancy));

        $application->load('stages');
        $interviewStage = $application->stages->firstWhere('key', 'wawancara_manajer_hr');
        $this->assertEquals(ApplicationStageStatus::Selesai, $interviewStage->status);
    }

    public function test_director_can_pass_candidate_at_director_interview(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $director = $this->makeDirector();
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_direktur']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_direktur');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_direktur');

        $response = $this->actingAs($director)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings]
        );

        $response->assertRedirect(route('lowongan.wawancara.index', $vacancy));

        $application->load('stages');
        $stage = $application->stages->firstWhere('key', 'wawancara_direktur');
        $this->assertEquals(ApplicationStageStatus::Selesai, $stage->status);
    }

    // ── Record Interview Result: Fail ─────────────────────────────────────────

    public function test_interviewer_can_fail_candidate(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'gagal', 'catatan' => 'Tidak memenuhi syarat.', 'ratings' => $ratings]
        );

        $response->assertRedirect(route('lowongan.wawancara.index', $vacancy));

        $application->load('stages');
        $stage = $application->stages->firstWhere('key', 'wawancara_kepala_unit');
        $this->assertEquals(ApplicationStageStatus::Gagal, $stage->status);
    }

    // ── Record Interview Result: Reserve ──────────────────────────────────────

    public function test_interviewer_can_reserve_candidate(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'reserved', 'ratings' => $ratings]
        );

        $response->assertRedirect(route('lowongan.wawancara.index', $vacancy));

        $application->load('stages');
        $stage = $application->stages->firstWhere('key', 'wawancara_kepala_unit');
        $this->assertEquals(ApplicationStageStatus::Reserved, $stage->status);
    }

    // ── Criteria Ratings Stored ───────────────────────────────────────────────

    public function test_criteria_ratings_are_stored_with_result(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');

        $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings]
        );

        $application->load('stages');
        $stage = $application->stages->firstWhere('key', 'wawancara_kepala_unit');
        $result = InterviewResult::where('application_stage_id', $stage->id)->first();

        $this->assertNotNull($result);
        $this->assertEquals(count($ratings), $result->ratings()->count());
        $this->assertDatabaseHas('interview_result_ratings', [
            'interview_result_id' => $result->id,
            'interview_template_id' => $ratings[0]['interview_template_id'],
            'nama_kriteria' => $ratings[0]['nama_kriteria'],
            'nilai' => $ratings[0]['nilai'],
        ]);
    }

    // ── Blocking When No Templates ────────────────────────────────────────────

    public function test_cannot_decide_when_no_templates_assigned(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus']
        );

        $response->assertSessionHasErrors('interview');
    }

    // ── Role-Based Access ─────────────────────────────────────────────────────

    public function test_unit_head_cannot_decide_on_hr_manager_interview_stage(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_manajer_hr');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');

        // Unit head resolves to wawancara_kepala_unit, which is Selesai here
        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings]
        );

        // wawancara_kepala_unit is Selesai so not advanceable → error
        $response->assertSessionHasErrors('interview');
    }

    public function test_hr_manager_cannot_decide_on_unit_head_interview_stage(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $hrManager = $this->makeHrManager();
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit', 'wawancara_manajer_hr']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_manajer_hr');

        // HR Manager resolves to wawancara_manajer_hr, which is Pending here
        $response = $this->actingAs($hrManager)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings]
        );

        $response->assertSessionHasErrors('interview');
    }

    public function test_cannot_submit_interview_result_twice(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');

        // First submission
        $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings]
        );

        // Manually reset stage to advanceable so the "already submitted" check triggers
        $application->load('stages');
        $stage = $application->stages->firstWhere('key', 'wawancara_kepala_unit');
        $stage->update(['status' => ApplicationStageStatus::Aktif]);

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'gagal', 'ratings' => $ratings]
        );

        $response->assertSessionHasErrors('interview');
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_decide_requires_keputusan(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['ratings' => $ratings]
        );

        $response->assertSessionHasErrors('keputusan');
    }

    public function test_decide_requires_ratings_when_templates_assigned(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus']
        );

        $response->assertSessionHasErrors('ratings');
    }

    public function test_rating_nilai_must_be_1_to_5(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $template = $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $item = $template->items->first();
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            [
                'keputusan' => 'lulus',
                'ratings' => [['interview_template_id' => $template->id, 'nama_kriteria' => $item->teks, 'nilai' => 6]],
            ]
        );

        $response->assertSessionHasErrors();
    }

    public function test_catatan_is_optional(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings]
        );

        $response->assertRedirect(route('lowongan.wawancara.index', $vacancy));

        $application->load('stages');
        $stage = $application->stages->firstWhere('key', 'wawancara_kepala_unit');
        $result = InterviewResult::where('application_stage_id', $stage->id)->first();
        $this->assertNull($result->catatan);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_unauthenticated_user_redirected_from_interview_dashboard(): void
    {
        $unit = Unit::factory()->create();
        $vacancy = Vacancy::factory()->published()->create(['unit_id' => $unit->id]);

        $response = $this->get(route('lowongan.wawancara.index', $vacancy));

        $response->assertRedirect(route('login'));
    }

    // ── Readiness Questions Display ───────────────────────────────────────────

    public function test_interview_form_shows_readiness_questions_after_scored_criteria(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $readinessTemplate = $this->assignReadinessTemplate($vacancy, ['wawancara_kepala_unit'], 'Kesiapan Perawat');

        $response = $this->actingAs($unitHead)->get(route('lowongan.wawancara.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertSee('Pertanyaan Kesiapan');
        $response->assertSee('Kesiapan Perawat');
        $response->assertSee($readinessTemplate->items->first()->teks);
    }

    // ── Readiness Answers Stored ──────────────────────────────────────────────

    public function test_readiness_answers_stored_in_same_transaction_as_ratings(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $this->assignReadinessTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');
        $readinessAnswers = $this->readinessAnswersFor($vacancy, 'wawancara_kepala_unit', true);

        $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings, 'readiness_answers' => $readinessAnswers]
        );

        $application->load('stages');
        $stage = $application->stages->firstWhere('key', 'wawancara_kepala_unit');
        $result = InterviewResult::where('application_stage_id', $stage->id)->first();

        $this->assertNotNull($result);
        $this->assertEquals(count($readinessAnswers), $result->readinessAnswers()->count());
        $this->assertDatabaseHas('interview_readiness_answers', [
            'interview_result_id' => $result->id,
            'pertanyaan' => $readinessAnswers[0]['pertanyaan'],
            'jawaban' => true,
        ]);
    }

    public function test_readiness_no_answer_stored_correctly(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $this->assignReadinessTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');
        $readinessAnswers = $this->readinessAnswersFor($vacancy, 'wawancara_kepala_unit', false);

        $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings, 'readiness_answers' => $readinessAnswers]
        );

        $application->load('stages');
        $stage = $application->stages->firstWhere('key', 'wawancara_kepala_unit');
        $result = InterviewResult::where('application_stage_id', $stage->id)->first();

        $this->assertNotNull($result);
        $this->assertDatabaseHas('interview_readiness_answers', [
            'interview_result_id' => $result->id,
            'jawaban' => false,
        ]);
    }

    // ── No-Blocking Behavior ──────────────────────────────────────────────────

    public function test_no_readiness_answer_does_not_block_lulus_decision(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $this->assignReadinessTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');
        $readinessAnswers = $this->readinessAnswersFor($vacancy, 'wawancara_kepala_unit', false);

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings, 'readiness_answers' => $readinessAnswers]
        );

        $response->assertRedirect(route('lowongan.wawancara.index', $vacancy));

        $application->load('stages');
        $stage = $application->stages->firstWhere('key', 'wawancara_kepala_unit');
        $this->assertEquals(ApplicationStageStatus::Selesai, $stage->status);
    }

    public function test_readiness_answers_required_when_kesiapan_templates_assigned(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit']);
        $this->assignReadinessTemplate($vacancy, ['wawancara_kepala_unit']);
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings]
        );

        $response->assertSessionHasErrors('readiness_answers');
    }

    // ── Prior Interview Display ───────────────────────────────────────────────

    public function test_prior_interview_shows_readiness_answers_grouped_by_template(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $hrManager = $this->makeHrManager();
        $vacancy = $this->createVacancy($unit);
        $this->assignCriteriaTemplate($vacancy, ['wawancara_kepala_unit', 'wawancara_manajer_hr']);
        $readinessTemplate = $this->assignReadinessTemplate($vacancy, ['wawancara_kepala_unit'], 'Kesiapan Umum');
        $application = $this->makeAtInterviewStage($vacancy, 'wawancara_kepala_unit');

        $unitHead = $this->makeUnitHead($unit);
        $ratings = $this->criteriaRatingsFor($vacancy, 'wawancara_kepala_unit');
        $readinessAnswers = $this->readinessAnswersFor($vacancy, 'wawancara_kepala_unit', true);

        $this->actingAs($unitHead)->post(
            route('lowongan.wawancara.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'ratings' => $ratings, 'readiness_answers' => $readinessAnswers]
        );

        $application->load('stages');
        $hrRatings = $this->criteriaRatingsFor($vacancy, 'wawancara_manajer_hr');
        $application->refresh()->load('stages');

        $response = $this->actingAs($hrManager)->get(route('lowongan.wawancara.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertSee('Hasil Wawancara Sebelumnya');
        $response->assertSee('Kesiapan Umum');
        $response->assertSee($readinessTemplate->items->first()->teks);
        $response->assertSee('Ya');
    }
}
