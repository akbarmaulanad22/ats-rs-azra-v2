<?php

namespace Tests\Feature;

use App\Enums\VacancyStatus;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Stage;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationStepValidationTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    private function publishedVacancy(): Vacancy
    {
        $template = WorkflowTemplate::factory()->create();
        $stage = Stage::where('key', 'lamaran')->firstOrFail();
        $template->stages()->attach($stage->id, ['position' => 1]);
        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->published()->create([
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function step1Payload(): array
    {
        return [
            'step' => 1,
            'nama_lengkap' => 'Budi Santoso',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'belum_menikah',
            'alamat_ktp' => 'Jl. Sudirman No. 1, Jakarta',
            'alamat_domisili' => 'Jl. Sudirman No. 1, Jakarta',
            'no_telepon' => '081234567890',
            'email' => 'budi@example.com',
            'no_ktp' => '3174012301900001',
        ];
    }

    private function validate(Vacancy $vacancy, array $payload)
    {
        return $this->postJson(route('karier.lamar.validate', $vacancy), $payload);
    }

    // ── Per-step pass / fail ──────────────────────────────────────────────────

    public function test_valid_step1_passes(): void
    {
        $this->seedStages();
        $vacancy = $this->publishedVacancy();

        $this->validate($vacancy, $this->step1Payload())
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_invalid_step1_returns_422_with_field_errors(): void
    {
        $this->seedStages();
        $vacancy = $this->publishedVacancy();

        $this->validate($vacancy, ['step' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nama_lengkap', 'email', 'no_ktp']);
    }

    public function test_step1_only_validates_step1_fields(): void
    {
        $this->seedStages();
        $vacancy = $this->publishedVacancy();

        // No formal_educations / cv / step-8 fields supplied — must still pass,
        // because only step-1 rules apply.
        $this->validate($vacancy, $this->step1Payload())->assertOk();
    }

    public function test_step3_requires_formal_and_informal_educations(): void
    {
        $this->seedStages();
        $vacancy = $this->publishedVacancy();

        $this->validate($vacancy, ['step' => 3])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['formal_educations', 'informal_educations']);
    }

    public function test_step5_fresh_graduate_does_not_require_work_experiences(): void
    {
        $this->seedStages();
        $vacancy = $this->publishedVacancy();

        $this->validate($vacancy, ['step' => 5, 'is_fresh_graduate' => '1'])->assertOk();
    }

    public function test_step5_non_fresh_graduate_requires_work_experiences(): void
    {
        $this->seedStages();
        $vacancy = $this->publishedVacancy();

        $this->validate($vacancy, ['step' => 5, 'is_fresh_graduate' => '0'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('work_experiences');
    }

    // ── Step 1 duplicate email ────────────────────────────────────────────────

    public function test_step1_flags_email_already_applied_to_this_vacancy(): void
    {
        $this->seedStages();
        $vacancy = $this->publishedVacancy();
        $candidate = Candidate::factory()->create(['email' => 'budi@example.com']);
        Application::factory()->create([
            'candidate_id' => $candidate->id,
            'vacancy_id' => $vacancy->id,
        ]);

        $this->validate($vacancy, $this->step1Payload())
            ->assertStatus(422)
            ->assertJsonValidationErrors('email')
            ->assertJsonPath('errors.email.0', 'Anda sudah pernah melamar lowongan ini.');
    }

    public function test_step1_allows_email_applied_to_a_different_vacancy(): void
    {
        $this->seedStages();
        $vacancyA = $this->publishedVacancy();
        $vacancyB = $this->publishedVacancy();
        $candidate = Candidate::factory()->create(['email' => 'budi@example.com']);
        Application::factory()->create([
            'candidate_id' => $candidate->id,
            'vacancy_id' => $vacancyA->id,
        ]);

        $this->validate($vacancyB, $this->step1Payload())->assertOk();
    }

    public function test_other_steps_do_not_run_duplicate_email_check(): void
    {
        $this->seedStages();
        $vacancy = $this->publishedVacancy();
        $candidate = Candidate::factory()->create(['email' => 'budi@example.com']);
        Application::factory()->create([
            'candidate_id' => $candidate->id,
            'vacancy_id' => $vacancy->id,
        ]);

        // Step 6 carries no email field and must not trigger the dup check.
        $this->validate($vacancy, [
            'step' => 6,
            'alasan_melamar' => 'Tertarik berkarir di RS Azra.',
            'gaji_diharapkan' => 6000000,
        ])->assertOk();
    }

    // ── Guards ────────────────────────────────────────────────────────────────

    public function test_invalid_step_number_rejected(): void
    {
        $this->seedStages();
        $vacancy = $this->publishedVacancy();

        $this->validate($vacancy, ['step' => 99])->assertStatus(422);
        $this->validate($vacancy, ['step' => 0])->assertStatus(422);
    }

    public function test_validate_returns_404_for_draft_vacancy(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->create(['status' => VacancyStatus::Draft]);

        $this->validate($vacancy, $this->step1Payload())->assertStatus(404);
    }

    public function test_validate_returns_404_for_expired_vacancy(): void
    {
        $this->seedStages();
        $vacancy = $this->publishedVacancy();
        $vacancy->update(['tenggat_lamaran' => now()->subDay()->format('Y-m-d')]);

        $this->validate($vacancy, $this->step1Payload())->assertStatus(404);
    }
}
