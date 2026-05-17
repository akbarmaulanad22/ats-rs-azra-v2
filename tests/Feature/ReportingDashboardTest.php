<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportingDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    private function createVacancyWithStages(array $stageKeys, ?Unit $unit = null): Vacancy
    {
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template): void {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->create([
            'workflow_template_snapshot_id' => $snapshot->id,
            'unit_id' => $unit?->id ?? Unit::factory()->create()->id,
            'jumlah_posisi' => 2,
        ]);
    }

    /**
     * @param  array<int, ApplicationStageStatus>  $stageStatuses
     */
    private function makeApplication(Vacancy $vacancy, array $stageStatuses = [], ?Carbon $createdAt = null): Application
    {
        $vacancy->load('workflowTemplateSnapshot.stages');

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => Candidate::factory()->create()->id,
        ]);

        if ($createdAt !== null) {
            DB::table('applications')->where('id', $application->id)->update(['created_at' => $createdAt]);
        }

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

    // ── Access control ────────────────────────────────────────────────────────

    public function test_hr_admin_sees_reporting_dashboard(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Rekrutmen');
        $response->assertSee('Corong Pipeline');
    }

    public function test_hr_manager_sees_simple_landing_not_reporting_dashboard(): void
    {
        $user = User::factory()->withRole(Role::HrManager)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Dashboard Rekrutmen');
        $response->assertSee('Selamat datang');
    }

    public function test_unit_head_sees_simple_landing_not_reporting_dashboard(): void
    {
        $user = User::factory()->withRole(Role::UnitHead)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Dashboard Rekrutmen');
    }

    public function test_director_sees_simple_landing_not_reporting_dashboard(): void
    {
        $user = User::factory()->withRole(Role::Director)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Dashboard Rekrutmen');
    }

    public function test_employee_sees_simple_landing_not_reporting_dashboard(): void
    {
        $user = User::factory()->withRole(Role::Employee)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Dashboard Rekrutmen');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    // ── Metric calculations ───────────────────────────────────────────────────

    public function test_total_applications_stat_is_accurate(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $this->makeApplication($vacancy);
        $this->makeApplication($vacancy);
        $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('3'); // total applications
    }

    public function test_in_process_counts_only_applications_with_active_or_reserved_stage(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        // active
        $this->makeApplication($vacancy);
        // failed at skrining
        $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Gagal,
        ]);
        // completed onboarding
        $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Selesai,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('sedang berjalan');
    }

    public function test_accepted_stat_counts_only_completed_onboarding(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Selesai,
        ]);
        $this->makeApplication($vacancy); // still in process

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('onboarding selesai');
    }

    public function test_pipeline_funnel_shows_cumulative_stage_counts(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        // Reached onboarding
        $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Aktif,
        ]);
        // Failed at skrining (never reached onboarding)
        $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Gagal,
        ]);
        // Still at skrining
        $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Corong Pipeline');
        $response->assertSee('Aplikasi');
        $response->assertSee('Skrining CV HR');
        $response->assertSee('Onboarding');
    }

    public function test_time_to_hire_calculates_average_days_correctly(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'onboarding']);

        $appliedAt = Carbon::parse('2026-01-01 08:00:00');
        $app = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
        ], $appliedAt);

        DB::table('application_stages')
            ->where('application_id', $app->id)
            ->where('key', 'onboarding')
            ->update(['updated_at' => $appliedAt->copy()->addDays(10)]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('10'); // 10.0 days
    }

    public function test_time_to_hire_averages_multiple_completed_applications(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'onboarding']);

        $base = Carbon::parse('2026-01-01 08:00:00');

        $app1 = $this->makeApplication($vacancy, [0 => ApplicationStageStatus::Selesai, 1 => ApplicationStageStatus::Selesai], $base);
        $app2 = $this->makeApplication($vacancy, [0 => ApplicationStageStatus::Selesai, 1 => ApplicationStageStatus::Selesai], $base);

        DB::table('application_stages')
            ->where('application_id', $app1->id)->where('key', 'onboarding')
            ->update(['updated_at' => $base->copy()->addDays(10)]);

        DB::table('application_stages')
            ->where('application_id', $app2->id)->where('key', 'onboarding')
            ->update(['updated_at' => $base->copy()->addDays(20)]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('15'); // avg of 10 and 20 = 15.0
    }

    public function test_time_to_hire_placeholder_when_no_completed_onboarding(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'onboarding']);

        $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Belum ada kandidat yang menyelesaikan onboarding');
    }

    public function test_stage_rates_shows_pass_fail_reserved_breakdown(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $this->makeApplication($vacancy, [0 => ApplicationStageStatus::Selesai, 1 => ApplicationStageStatus::Selesai, 2 => ApplicationStageStatus::Aktif]);
        $this->makeApplication($vacancy, [0 => ApplicationStageStatus::Selesai, 1 => ApplicationStageStatus::Gagal]);
        $this->makeApplication($vacancy, [0 => ApplicationStageStatus::Selesai, 1 => ApplicationStageStatus::Reserved]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Tingkat Lulus / Gagal per Tahap');
        $response->assertSee('Skrining CV HR');
    }

    public function test_stage_bottleneck_shows_average_days_per_stage(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $base = Carbon::parse('2026-01-01 08:00:00');
        $app = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Selesai,
        ], $base);

        DB::table('application_stages')
            ->where('application_id', $app->id)->where('key', 'aplikasi')
            ->update(['created_at' => $base, 'updated_at' => $base]);

        DB::table('application_stages')
            ->where('application_id', $app->id)->where('key', 'skrining_cv_hr')
            ->update(['created_at' => $base, 'updated_at' => $base->copy()->addDays(5)]);

        DB::table('application_stages')
            ->where('application_id', $app->id)->where('key', 'onboarding')
            ->update(['created_at' => $base, 'updated_at' => $base->copy()->addDays(8)]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Hambatan Tahap');
        $response->assertSee('hr'); // "hr" (hari) unit label
    }

    public function test_vacancy_summary_shows_applicant_count_and_filled_positions(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'onboarding']);

        $this->makeApplication($vacancy, [0 => ApplicationStageStatus::Selesai, 1 => ApplicationStageStatus::Selesai]);
        $this->makeApplication($vacancy);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Ringkasan Lowongan');
        $response->assertSee($vacancy->judul_posisi);
        $response->assertSee('/ 2'); // jumlah_posisi = 2
    }

    public function test_dashboard_empty_state_when_no_applications(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Belum ada data lamaran');
    }

    // ── Filter functionality ──────────────────────────────────────────────────

    public function test_date_from_filter_excludes_earlier_applications(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $this->makeApplication($vacancy, [], Carbon::parse('2025-01-15'));
        $this->makeApplication($vacancy, [], Carbon::parse('2026-03-01'));

        $response = $this->actingAs($admin)->get(route('dashboard', ['date_from' => '2026-01-01']));

        $response->assertOk();
        $response->assertSee('1'); // only the 2026 application
    }

    public function test_date_to_filter_excludes_later_applications(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancy = $this->createVacancyWithStages(['aplikasi', 'skrining_cv_hr', 'onboarding']);

        $this->makeApplication($vacancy, [], Carbon::parse('2025-06-01'));
        $this->makeApplication($vacancy, [], Carbon::parse('2026-03-01'));

        $response = $this->actingAs($admin)->get(route('dashboard', ['date_to' => '2025-12-31']));

        $response->assertOk();
        $response->assertSee('1'); // only the 2025 application
    }

    public function test_unit_filter_scopes_metrics_to_selected_unit(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unitA = Unit::factory()->create(['nama' => 'Unit Alpha Test']);
        $unitB = Unit::factory()->create(['nama' => 'Unit Beta Test']);

        $vacancyA = $this->createVacancyWithStages(['aplikasi', 'onboarding'], $unitA);
        $vacancyB = $this->createVacancyWithStages(['aplikasi', 'onboarding'], $unitB);

        $this->makeApplication($vacancyA);
        $this->makeApplication($vacancyA);
        $this->makeApplication($vacancyB);

        $response = $this->actingAs($admin)->get(route('dashboard', ['unit_id' => $unitA->id]));

        $response->assertOk();
        $response->assertSee('2'); // only Unit A applications
    }

    public function test_vacancy_filter_scopes_metrics_to_selected_vacancy(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $vacancyA = $this->createVacancyWithStages(['aplikasi', 'onboarding']);
        $vacancyB = $this->createVacancyWithStages(['aplikasi', 'onboarding']);

        $this->makeApplication($vacancyA);
        $this->makeApplication($vacancyA);
        $this->makeApplication($vacancyB);

        $response = $this->actingAs($admin)->get(route('dashboard', ['vacancy_id' => $vacancyA->id]));

        $response->assertOk();
        $response->assertSee('2'); // only vacancyA applications
    }

    public function test_invalid_unit_id_returns_validation_error(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard', ['unit_id' => 99999]));

        $response->assertSessionHasErrors('unit_id');
    }

    public function test_date_to_before_date_from_returns_validation_error(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard', [
            'date_from' => '2026-06-01',
            'date_to' => '2026-01-01',
        ]));

        $response->assertSessionHasErrors('date_to');
    }
}
