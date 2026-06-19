<?php

namespace Tests\Feature;

use App\Enums\EmploymentType;
use App\Enums\JobTemplateStatus;
use App\Enums\Role;
use App\Enums\VacancyStatus;
use App\Models\InterviewTemplate;
use App\Models\JobTemplate;
use App\Models\JobTemplateTest;
use App\Models\Question;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobTemplateManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function workflowWithStages(array $stageKeys = ['lamaran', 'wawancara_user', 'onboarding']): WorkflowTemplate
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);

        $template = WorkflowTemplate::factory()->create();
        Stage::whereIn('key', $stageKeys)->get()->each(function (Stage $stage, int $index) use ($template) {
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        return $template->load('stages');
    }

    private function templatePayload(?Unit $unit = null, ?WorkflowTemplate $workflow = null): array
    {
        return [
            'judul_posisi' => 'Perawat ICU',
            'unit_id' => ($unit ?? Unit::factory()->create())->id,
            'workflow_template_id' => ($workflow ?? WorkflowTemplate::factory()->create())->id,
            'jenis_pekerjaan' => EmploymentType::FullTime->value,
            'deskripsi_pekerjaan' => 'Merawat pasien di ICU.',
            'kualifikasi' => 'S1 Keperawatan.',
        ];
    }

    private function publishPayload(): array
    {
        return [
            'jumlah_posisi' => 2,
            'tenggat_lamaran' => now()->addMonth()->format('Y-m-d'),
            'flyer' => UploadedFile::fake()->image('flyer.jpg', 600, 800),
            'status' => VacancyStatus::Draft->value,
        ];
    }

    // ── View rendering (smoke) ──────────────────────────────────────────────

    public function test_all_job_template_pages_render(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create([
            'workflow_template_id' => $this->workflowWithStages()->id,
        ]);

        // index with a row exercises the action-dropdown route() calls
        $this->actingAs($admin)->get(route('template-lowongan.index'))->assertOk();
        $this->actingAs($admin)->get(route('template-lowongan.create'))->assertOk();
        $this->actingAs($admin)->get(route('template-lowongan.edit', $template))->assertOk();
        $this->actingAs($admin)->get(route('template-lowongan.terbitkan.form', $template))->assertOk();
        $this->actingAs($admin)->get(route('template-lowongan.tes.show', $template))->assertOk();
        $this->actingAs($admin)->get(route('template-lowongan.template-wawancara.show', $template))->assertOk();
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_index(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $this->actingAs($admin)->get(route('template-lowongan.index'))->assertOk();
    }

    public function test_non_hr_admin_cannot_view_index(): void
    {
        $user = User::factory()->create(['role' => Role::HrManager]);

        $this->actingAs($user)->get(route('template-lowongan.index'))->assertForbidden();
    }

    public function test_hr_admin_can_create_template(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('template-lowongan.store'), $this->templatePayload());

        $response->assertRedirect(route('template-lowongan.index'));
        $this->assertDatabaseHas('job_templates', [
            'judul_posisi' => 'Perawat ICU',
            'status' => JobTemplateStatus::Active->value,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $this->actingAs($admin)->post(route('template-lowongan.store'), [])
            ->assertSessionHasErrors(['judul_posisi', 'unit_id', 'workflow_template_id', 'jenis_pekerjaan', 'deskripsi_pekerjaan', 'kualifikasi']);
    }

    public function test_non_hr_admin_cannot_create_template(): void
    {
        $user = User::factory()->create(['role' => Role::HrManager]);

        $this->actingAs($user)->post(route('template-lowongan.store'), $this->templatePayload())
            ->assertForbidden();
    }

    public function test_hr_admin_can_update_template_status(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();

        $payload = $this->templatePayload();
        $payload['judul_posisi'] = 'Perawat IGD';
        $payload['status'] = JobTemplateStatus::Archived->value;

        $this->actingAs($admin)->put(route('template-lowongan.update', $template), $payload)
            ->assertRedirect(route('template-lowongan.index'));

        $this->assertSame('Perawat IGD', $template->fresh()->judul_posisi);
        $this->assertSame(JobTemplateStatus::Archived, $template->fresh()->status);
    }

    public function test_hr_admin_can_delete_template_without_vacancies(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();

        $this->actingAs($admin)->delete(route('template-lowongan.destroy', $template))
            ->assertRedirect(route('template-lowongan.index'));

        $this->assertDatabaseMissing('job_templates', ['id' => $template->id]);
    }

    public function test_deleting_template_with_vacancies_is_blocked(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        Vacancy::factory()->create(['job_template_id' => $template->id]);

        $this->actingAs($admin)->delete(route('template-lowongan.destroy', $template))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('job_templates', ['id' => $template->id]);
    }

    // ── Publish ───────────────────────────────────────────────────────────────

    public function test_publish_creates_vacancy_from_template(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create([
            'workflow_template_id' => $this->workflowWithStages()->id,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('template-lowongan.terbitkan', $template), $this->publishPayload());

        $response->assertRedirect(route('lowongan.index'));
        $this->assertDatabaseHas('vacancies', [
            'job_template_id' => $template->id,
            'judul_posisi' => $template->judul_posisi,
            'status' => VacancyStatus::Draft->value,
        ]);
        Storage::disk('public')->assertExists(Vacancy::first()->flyer_path);
    }

    public function test_publish_blocks_published_status_when_test_stage_unconfigured(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create([
            'workflow_template_id' => $this->workflowWithStages(['lamaran', 'tes_kompetensi', 'onboarding'])->id,
        ]);

        $payload = $this->publishPayload();
        $payload['status'] = VacancyStatus::Published->value;

        $this->actingAs($admin)->post(route('template-lowongan.terbitkan', $template), $payload)
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('vacancies', 0);
    }

    public function test_publish_allows_published_status_when_test_configured(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create([
            'workflow_template_id' => $this->workflowWithStages(['lamaran', 'tes_kompetensi', 'onboarding'])->id,
        ]);
        $test = JobTemplateTest::factory()->create(['job_template_id' => $template->id]);
        $test->questions()->attach(Question::factory()->create()->id, ['urutan' => 1]);

        $payload = $this->publishPayload();
        $payload['status'] = VacancyStatus::Published->value;

        $this->actingAs($admin)->post(route('template-lowongan.terbitkan', $template), $payload)
            ->assertRedirect(route('lowongan.index'));

        $this->assertDatabaseHas('vacancies', [
            'job_template_id' => $template->id,
            'status' => VacancyStatus::Published->value,
        ]);
        $this->assertDatabaseCount('vacancy_test_snapshots', 1);
    }

    public function test_non_hr_admin_cannot_publish(): void
    {
        $user = User::factory()->create(['role' => Role::HrManager]);
        $template = JobTemplate::factory()->create([
            'workflow_template_id' => $this->workflowWithStages()->id,
        ]);

        $this->actingAs($user)->post(route('template-lowongan.terbitkan', $template), $this->publishPayload())
            ->assertForbidden();
    }

    // ── Test config ───────────────────────────────────────────────────────────

    public function test_hr_admin_can_save_test_config(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $questions = Question::factory()->count(2)->create();

        $this->actingAs($admin)->post(route('template-lowongan.tes.save', $template), [
            'batas_waktu_menit' => 90,
            'question_ids' => $questions->pluck('id')->all(),
        ])->assertRedirect(route('template-lowongan.tes.show', $template));

        $this->assertDatabaseHas('job_template_tests', [
            'job_template_id' => $template->id,
            'batas_waktu_menit' => 90,
        ]);
        $this->assertDatabaseCount('job_template_test_questions', 2);
    }

    // ── Interview config ──────────────────────────────────────────────────────

    public function test_hr_admin_can_save_interview_templates(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create([
            'workflow_template_id' => $this->workflowWithStages()->id,
        ]);
        $interview = InterviewTemplate::factory()->create();

        $this->actingAs($admin)->post(route('template-lowongan.template-wawancara.save', $template), [
            'assignments' => ['wawancara_user' => [$interview->id]],
        ])->assertRedirect(route('template-lowongan.template-wawancara.show', $template));

        $this->assertDatabaseHas('job_template_interview_templates', [
            'job_template_id' => $template->id,
            'interview_template_id' => $interview->id,
            'stage_key' => 'wawancara_user',
        ]);
    }
}
