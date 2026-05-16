<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\Employee;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CvScreeningTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    private function createVacancy(Unit $unit, array $stageKeys = ['aplikasi', 'skrining_cv_hr', 'skrining_cv_kepala_unit', 'onboarding']): Vacancy
    {
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->published()->create([
            'unit_id' => $unit->id,
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);
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

    private function makeUnitHead(Unit $unit): User
    {
        $user = User::factory()->withRole(Role::UnitHead)->create();
        Employee::factory()->create([
            'user_id' => $user->id,
            'unit' => $unit->nama,
        ]);

        return $user;
    }

    // ── Index (screening list) ────────────────────────────────────────────────

    public function test_hr_admin_can_view_screening_list(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->get(route('lowongan.skrining.index', $vacancy));

        $response->assertOk();
        $response->assertViewIs('screening.index');
    }

    public function test_unit_head_can_view_screening_list_for_own_unit(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Aktif,
            3 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->actingAs($unitHead)->get(route('lowongan.skrining.index', $vacancy));

        $response->assertOk();
    }

    public function test_unit_head_cannot_view_screening_for_different_unit(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $otherUnit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($otherUnit);

        $response = $this->actingAs($unitHead)->get(route('lowongan.skrining.index', $vacancy));

        $response->assertForbidden();
    }

    public function test_unit_head_without_employee_record_cannot_view_screening(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = User::factory()->withRole(Role::UnitHead)->create();
        $vacancy = $this->createVacancy($unit);

        $response = $this->actingAs($unitHead)->get(route('lowongan.skrining.index', $vacancy));

        $response->assertForbidden();
    }

    public function test_employee_role_cannot_view_screening(): void
    {
        $this->seedStages();
        $employee = User::factory()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);

        $response = $this->actingAs($employee)->get(route('lowongan.skrining.index', $vacancy));

        $response->assertForbidden();
    }

    public function test_screening_list_shows_candidates_at_hr_screening_stage(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->get(route('lowongan.skrining.index', $vacancy));

        $response->assertOk();
        $response->assertSee($application->candidate->nama_lengkap);
    }

    public function test_screening_list_filter_by_pending_status(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->get(
            route('lowongan.skrining.index', ['lowongan' => $vacancy->id, 'status' => 'aktif'])
        );

        $response->assertOk();
        $response->assertSee($application->candidate->nama_lengkap);
    }

    public function test_screening_list_filter_hides_candidates_not_matching_status(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->get(
            route('lowongan.skrining.index', ['lowongan' => $vacancy->id, 'status' => 'selesai'])
        );

        $response->assertOk();
        $response->assertDontSee($application->candidate->nama_lengkap);
    }

    // ── Show (candidate detail) ───────────────────────────────────────────────

    public function test_hr_admin_can_view_candidate_detail(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->get(route('lowongan.skrining.show', [$vacancy, $application]));

        $response->assertOk();
        $response->assertViewIs('screening.show');
        $response->assertSee($application->candidate->nama_lengkap);
    }

    public function test_unit_head_can_view_candidate_detail_for_own_unit(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Aktif,
            3 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->actingAs($unitHead)->get(route('lowongan.skrining.show', [$vacancy, $application]));

        $response->assertOk();
    }

    public function test_unit_head_cannot_view_candidate_detail_for_other_unit(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $otherUnit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($otherUnit);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($unitHead)->get(route('lowongan.skrining.show', [$vacancy, $application]));

        $response->assertForbidden();
    }

    // ── Decision: Pass (lulus) ────────────────────────────────────────────────

    public function test_hr_admin_can_pass_candidate_at_hr_screening(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->post(
            route('lowongan.skrining.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'catatan' => 'CV bagus.']
        );

        $response->assertRedirect(route('lowongan.skrining.index', $vacancy));

        $application->load('stages');
        $skriningStage = $application->stages->firstWhere('key', 'skrining_cv_hr');
        $nextStage = $application->stages->firstWhere('key', 'skrining_cv_kepala_unit');

        $this->assertEquals(ApplicationStageStatus::Selesai, $skriningStage->status);
        $this->assertEquals('CV bagus.', $skriningStage->catatan);
        $this->assertEquals(ApplicationStageStatus::Aktif, $nextStage->status);
    }

    public function test_hr_admin_can_fail_candidate_at_hr_screening(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->post(
            route('lowongan.skrining.keputusan', [$vacancy, $application]),
            ['keputusan' => 'gagal', 'catatan' => 'Tidak memenuhi kualifikasi.']
        );

        $response->assertRedirect(route('lowongan.skrining.index', $vacancy));

        $application->load('stages');
        $skriningStage = $application->stages->firstWhere('key', 'skrining_cv_hr');

        $this->assertEquals(ApplicationStageStatus::Gagal, $skriningStage->status);
        $this->assertEquals('Tidak memenuhi kualifikasi.', $skriningStage->catatan);
    }

    public function test_hr_admin_can_reserve_candidate_at_hr_screening(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->post(
            route('lowongan.skrining.keputusan', [$vacancy, $application]),
            ['keputusan' => 'reserved', 'catatan' => 'Dipertimbangkan.']
        );

        $response->assertRedirect(route('lowongan.skrining.index', $vacancy));

        $application->load('stages');
        $skriningStage = $application->stages->firstWhere('key', 'skrining_cv_hr');
        $nextStage = $application->stages->firstWhere('key', 'skrining_cv_kepala_unit');

        $this->assertEquals(ApplicationStageStatus::Reserved, $skriningStage->status);
        $this->assertEquals('Dipertimbangkan.', $skriningStage->catatan);
        $this->assertEquals(ApplicationStageStatus::Pending, $nextStage->status);
    }

    // ── Unit Head screening flow ──────────────────────────────────────────────

    public function test_unit_head_can_pass_candidate_at_unit_head_screening(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Aktif,
            3 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->actingAs($unitHead)->post(
            route('lowongan.skrining.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus', 'catatan' => 'Kandidat potensial.']
        );

        $response->assertRedirect(route('lowongan.skrining.index', $vacancy));

        $application->load('stages');
        $unitHeadStage = $application->stages->firstWhere('key', 'skrining_cv_kepala_unit');
        $this->assertEquals(ApplicationStageStatus::Selesai, $unitHeadStage->status);
        $this->assertEquals('Kandidat potensial.', $unitHeadStage->catatan);
    }

    public function test_unit_head_sees_only_candidates_at_unit_head_screening_stage(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);

        $hrPendingApplication = $this->makeApplication($vacancy);
        $hrPassedApplication = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Aktif,
            3 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->actingAs($unitHead)->get(route('lowongan.skrining.index', $vacancy));

        $response->assertOk();
        $response->assertSee($hrPassedApplication->candidate->nama_lengkap);
        $response->assertDontSee($hrPendingApplication->candidate->nama_lengkap);
    }

    public function test_unit_head_cannot_decide_on_hr_screening_stage_application(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $unitHead = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy);

        // candidate only has skrining_cv_hr active; unit head's stageKey is skrining_cv_kepala_unit
        // skrining_cv_kepala_unit stage exists but is Pending → 403
        $response = $this->actingAs($unitHead)->post(
            route('lowongan.skrining.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus']
        );

        $response->assertForbidden();
    }

    // ── Sequential access ─────────────────────────────────────────────────────

    public function test_decision_cannot_be_submitted_twice(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Gagal,
            2 => ApplicationStageStatus::Pending,
            3 => ApplicationStageStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->post(
            route('lowongan.skrining.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus']
        );

        $response->assertSessionHasErrors('screening');
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_decision_requires_keputusan_field(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->post(
            route('lowongan.skrining.keputusan', [$vacancy, $application]),
            []
        );

        $response->assertSessionHasErrors('keputusan');
    }

    public function test_decision_rejects_invalid_keputusan_value(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->post(
            route('lowongan.skrining.keputusan', [$vacancy, $application]),
            ['keputusan' => 'invalid-value']
        );

        $response->assertSessionHasErrors('keputusan');
    }

    public function test_catatan_is_optional(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $vacancy = $this->createVacancy($unit);
        $application = $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->post(
            route('lowongan.skrining.keputusan', [$vacancy, $application]),
            ['keputusan' => 'lulus']
        );

        $response->assertRedirect(route('lowongan.skrining.index', $vacancy));

        $application->load('stages');
        $skriningStage = $application->stages->firstWhere('key', 'skrining_cv_hr');
        $this->assertNull($skriningStage->catatan);
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected(): void
    {
        $unit = Unit::factory()->create();
        $vacancy = Vacancy::factory()->published()->create(['unit_id' => $unit->id]);

        $response = $this->get(route('lowongan.skrining.index', $vacancy));

        $response->assertRedirect(route('login'));
    }
}
