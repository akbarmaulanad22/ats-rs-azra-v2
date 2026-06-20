<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\VacancyStatus;
use App\Mail\TemplatedMail;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\JobTemplate;
use App\Models\OnboardingResult;
use App\Models\Stage;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CallbackInviteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    /**
     * @param  list<string>  $stageKeys
     * @param  array<string, mixed>  $overrides
     */
    private function vacancyWithStages(
        int $jobTemplateId,
        array $stageKeys = ['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding'],
        array $overrides = [],
    ): Vacancy {
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->published()->create(array_merge([
            'job_template_id' => $jobTemplateId,
            'workflow_template_snapshot_id' => $snapshot->id,
        ], $overrides));
    }

    private function gagalApplication(Vacancy $vacancy, Candidate $candidate, string $gagalKey): Application
    {
        $vacancy->load('workflowTemplateSnapshot.stages');

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $candidate->id,
        ]);

        // Mirror production: failing only updates the active stage to Gagal;
        // stages before it are Selesai, stages after remain Pending.
        $passedGagal = false;

        foreach ($vacancy->workflowTemplateSnapshot->stages->sortBy('position') as $stage) {
            if ($stage->key === $gagalKey) {
                $status = ApplicationStageStatus::Gagal;
                $passedGagal = true;
            } elseif ($passedGagal) {
                $status = ApplicationStageStatus::Pending;
            } else {
                $status = ApplicationStageStatus::Selesai;
            }

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

    private function activeApplication(Vacancy $vacancy, Candidate $candidate): Application
    {
        $vacancy->load('workflowTemplateSnapshot.stages');

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $candidate->id,
        ]);

        foreach ($vacancy->workflowTemplateSnapshot->stages->sortBy('position') as $index => $stage) {
            ApplicationStage::factory()->create([
                'application_id' => $application->id,
                'position' => $stage->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $index === 0 ? ApplicationStageStatus::Aktif : ApplicationStageStatus::Pending,
            ]);
        }

        return $application;
    }

    public function test_lists_prior_gagal_from_same_template_only(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $prior = $this->vacancyWithStages($template->id);
        $target = $this->vacancyWithStages($template->id);

        $sameTemplate = Candidate::factory()->create(['nama_lengkap' => 'Budi Same']);
        $this->gagalApplication($prior, $sameTemplate, 'wawancara_user');

        $otherTemplate = JobTemplate::factory()->create();
        $otherVacancy = $this->vacancyWithStages($otherTemplate->id);
        $outsider = Candidate::factory()->create(['nama_lengkap' => 'Citra Other']);
        $this->gagalApplication($otherVacancy, $outsider, 'wawancara_user');

        $response = $this->actingAs($admin)->get(route('callback.index', $target));

        $response->assertOk();
        $response->assertSee('Budi Same');
        $response->assertDontSee('Citra Other');
    }

    public function test_default_filter_hides_screening_failures_and_widen_shows_them(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $prior = $this->vacancyWithStages($template->id);
        $target = $this->vacancyWithStages($template->id);

        $screened = Candidate::factory()->create(['nama_lengkap' => 'Dewi Screening']);
        $this->gagalApplication($prior, $screened, 'skrining_cv_hr');

        $this->actingAs($admin)->get(route('callback.index', $target))
            ->assertDontSee('Dewi Screening');

        $this->actingAs($admin)->get(route('callback.index', ['lowongan' => $target, 'screening' => 1]))
            ->assertSee('Dewi Screening');
    }

    public function test_excludes_hired_candidate(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $prior = $this->vacancyWithStages($template->id);
        $target = $this->vacancyWithStages($template->id);

        $hired = Candidate::factory()->create(['nama_lengkap' => 'Eka Hired']);
        $failedApp = $this->gagalApplication($prior, $hired, 'wawancara_user');
        OnboardingResult::create([
            'application_id' => $failedApp->id,
            'tanggal_bergabung' => now(),
            'sent_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('callback.index', $target))
            ->assertDontSee('Eka Hired');
    }

    public function test_excludes_self_applied_without_invite(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $prior = $this->vacancyWithStages($template->id);
        $target = $this->vacancyWithStages($template->id);

        $selfApplied = Candidate::factory()->create(['nama_lengkap' => 'Fajar Self']);
        $this->gagalApplication($prior, $selfApplied, 'wawancara_user');
        Application::factory()->create([
            'vacancy_id' => $target->id,
            'candidate_id' => $selfApplied->id,
        ]);

        $this->actingAs($admin)->get(route('callback.index', $target))
            ->assertDontSee('Fajar Self');
    }

    public function test_invite_creates_record_dedups_and_sends_email(): void
    {
        Mail::fake();
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $prior = $this->vacancyWithStages($template->id);
        $target = $this->vacancyWithStages($template->id);

        $candidate = Candidate::factory()->create();
        $this->gagalApplication($prior, $candidate, 'wawancara_user');

        $response = $this->actingAs($admin)->post(route('callback.invite', $target), [
            'candidate_ids' => [$candidate->id, $candidate->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('callback_invites', [
            'vacancy_id' => $target->id,
            'candidate_id' => $candidate->id,
            'invited_by' => $admin->id,
        ]);
        $this->assertEquals(1, $target->callbackInvites()->count());
        Mail::assertQueued(TemplatedMail::class, 1);
    }

    public function test_invite_rejects_oversized_candidate_list(): void
    {
        Mail::fake();
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $target = $this->vacancyWithStages($template->id);

        $this->actingAs($admin)->post(route('callback.invite', $target), [
            'candidate_ids' => range(1, 201),
        ])->assertSessionHasErrors('candidate_ids');

        $this->assertEquals(0, $target->callbackInvites()->count());
        Mail::assertNothingQueued();
    }

    public function test_invite_on_closed_vacancy_is_rejected(): void
    {
        Mail::fake();
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $closed = $this->vacancyWithStages($template->id, overrides: [
            'status' => VacancyStatus::Closed,
        ]);

        $candidate = Candidate::factory()->create();

        $this->actingAs($admin)->post(route('callback.invite', $closed), [
            'candidate_ids' => [$candidate->id],
        ])->assertSessionHasErrors('callback');

        $this->assertEquals(0, $closed->callbackInvites()->count());
        Mail::assertNothingQueued();
    }

    public function test_invite_skips_ineligible_candidate_not_in_list(): void
    {
        Mail::fake();
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $prior = $this->vacancyWithStages($template->id);
        $target = $this->vacancyWithStages($template->id);

        $eligible = Candidate::factory()->create();
        $this->gagalApplication($prior, $eligible, 'wawancara_user');

        // Hired candidate: real id, excluded from the list. A crafted POST must
        // not invite them.
        $hired = Candidate::factory()->create();
        $hiredApp = $this->gagalApplication($prior, $hired, 'wawancara_user');
        OnboardingResult::create([
            'application_id' => $hiredApp->id,
            'tanggal_bergabung' => now(),
            'sent_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('callback.invite', $target), [
            'candidate_ids' => [$eligible->id, $hired->id],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('callback_invites', ['candidate_id' => $eligible->id]);
        $this->assertDatabaseMissing('callback_invites', ['candidate_id' => $hired->id]);
        $this->assertEquals(1, $target->callbackInvites()->count());
        Mail::assertQueued(TemplatedMail::class, 1);
    }

    public function test_invite_rejected_when_no_candidate_is_eligible(): void
    {
        Mail::fake();
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $target = $this->vacancyWithStages($template->id);

        // Candidate with no prior Gagal application under this template.
        $outsider = Candidate::factory()->create();

        $this->actingAs($admin)->post(route('callback.invite', $target), [
            'candidate_ids' => [$outsider->id],
        ])->assertSessionHasErrors('callback');

        $this->assertEquals(0, $target->callbackInvites()->count());
        Mail::assertNothingQueued();
    }

    public function test_resend_is_idempotent(): void
    {
        Mail::fake();
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $prior = $this->vacancyWithStages($template->id);
        $target = $this->vacancyWithStages($template->id);

        $candidate = Candidate::factory()->create();
        $this->gagalApplication($prior, $candidate, 'wawancara_user');

        $this->actingAs($admin)->post(route('callback.invite', $target), ['candidate_ids' => [$candidate->id]]);
        $this->actingAs($admin)->post(route('callback.invite', $target), ['candidate_ids' => [$candidate->id]]);

        $this->assertEquals(1, $target->callbackInvites()->count());
    }

    public function test_responded_badge_after_invited_candidate_applies(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $prior = $this->vacancyWithStages($template->id);
        $target = $this->vacancyWithStages($template->id);

        $candidate = Candidate::factory()->create(['nama_lengkap' => 'Gita Responded']);
        $this->gagalApplication($prior, $candidate, 'wawancara_user');

        $target->callbackInvites()->create([
            'candidate_id' => $candidate->id,
            'invited_by' => $admin->id,
            'invited_at' => now(),
        ]);
        Application::factory()->create([
            'vacancy_id' => $target->id,
            'candidate_id' => $candidate->id,
        ]);

        $this->actingAs($admin)->get(route('callback.index', $target))
            ->assertSee('Gita Responded')
            ->assertSee('Sudah melamar');
    }

    public function test_closed_vacancy_routes_to_publish_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $closed = $this->vacancyWithStages($template->id, overrides: [
            'status' => VacancyStatus::Closed,
        ]);

        $this->actingAs($admin)->get(route('callback.index', $closed))
            ->assertRedirect(route('template-lowongan.terbitkan.form', [$template, 'callback' => 1]));
    }

    public function test_archived_template_blocks_callback_on_closed_vacancy(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->archived()->create();
        $closed = $this->vacancyWithStages($template->id, overrides: [
            'status' => VacancyStatus::Closed,
        ]);

        $this->actingAs($admin)->get(route('callback.index', $closed))
            ->assertRedirect(route('lowongan.index'));
    }

    public function test_no_active_badge_when_candidate_only_has_the_failed_application(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $prior = $this->vacancyWithStages($template->id);
        $target = $this->vacancyWithStages($template->id);

        // Fails mid-pipeline; trailing stages stay Pending. Must NOT read as active.
        $candidate = Candidate::factory()->create(['nama_lengkap' => 'Hadi Lonely']);
        $this->gagalApplication($prior, $candidate, 'wawancara_user');

        $this->actingAs($admin)->get(route('callback.index', $target))
            ->assertSee('Hadi Lonely')
            ->assertDontSee('Aktif di lowongan lain');
    }

    public function test_active_badge_when_candidate_has_live_application_elsewhere(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = JobTemplate::factory()->create();
        $prior = $this->vacancyWithStages($template->id);
        $target = $this->vacancyWithStages($template->id);

        $candidate = Candidate::factory()->create(['nama_lengkap' => 'Indra Active']);
        $this->gagalApplication($prior, $candidate, 'wawancara_user');

        // A separate, in-progress application in an unrelated open vacancy.
        $elsewhere = $this->vacancyWithStages(JobTemplate::factory()->create()->id);
        $this->activeApplication($elsewhere, $candidate);

        $this->actingAs($admin)->get(route('callback.index', $target))
            ->assertSee('Indra Active')
            ->assertSee('Aktif di lowongan lain');
    }

    public function test_non_hr_admin_is_forbidden(): void
    {
        $employee = User::factory()->create();
        $template = JobTemplate::factory()->create();
        $target = $this->vacancyWithStages($template->id);

        $this->actingAs($employee)->get(route('callback.index', $target))
            ->assertForbidden();
    }
}
