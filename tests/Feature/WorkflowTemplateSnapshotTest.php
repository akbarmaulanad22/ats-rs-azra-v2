<?php

namespace Tests\Feature;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTemplateSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    private function createTemplateWithStages(string $name = 'Template A', array $stageKeys = ['lamaran', 'skrining_cv_hr', 'onboarding']): WorkflowTemplate
    {
        $template = WorkflowTemplate::factory()->create(['nama' => $name]);
        $stages = Stage::whereIn('key', $stageKeys)->get();

        $stages->each(function (Stage $stage, int $index) use ($template) {
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        return $template->load('stages');
    }

    private function vacancyPayload(Unit $unit, WorkflowTemplate $template): array
    {
        return [
            'judul_posisi' => 'Perawat ICU',
            'unit_id' => $unit->id,
            'workflow_template_id' => $template->id,
            'jenis_pekerjaan' => EmploymentType::FullTime->value,
            'deskripsi_pekerjaan' => 'Merawat pasien.',
            'kualifikasi' => 'S1 Keperawatan.',
            'jumlah_posisi' => 1,
            'tenggat_lamaran' => now()->addMonth()->format('Y-m-d'),
            'status' => VacancyStatus::Draft->value,
        ];
    }

    // ── Snapshot creation ────────────────────────────────────────────────────

    public function test_snapshot_captures_template_name(): void
    {
        $this->seedStages();
        $template = $this->createTemplateWithStages('Alur Perawat');

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $this->assertEquals('Alur Perawat', $snapshot->nama);
    }

    public function test_snapshot_captures_all_stages_with_correct_data(): void
    {
        $this->seedStages();
        $template = $this->createTemplateWithStages('Template B', ['lamaran', 'skrining_cv_hr', 'onboarding']);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);
        $stages = $snapshot->fresh()->stages;

        $this->assertCount(3, $stages);

        $first = $stages->firstWhere('key', 'lamaran');
        $this->assertNotNull($first);
        $this->assertEquals('Lamaran', $first->nama);
        $this->assertTrue($first->is_locked_first);
        $this->assertFalse($first->is_locked_last);

        $last = $stages->firstWhere('key', 'onboarding');
        $this->assertNotNull($last);
        $this->assertEquals('Onboarding', $last->nama);
        $this->assertTrue($last->is_locked_last);
        $this->assertFalse($last->is_locked_first);
    }

    public function test_snapshot_has_no_foreign_key_to_master_template(): void
    {
        $this->seedStages();
        $template = $this->createTemplateWithStages();

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $columns = \Schema::getColumnListing('workflow_template_snapshots');
        $this->assertNotContains('workflow_template_id', $columns);
        $this->assertNotContains('original_template_id', $columns);
    }

    public function test_snapshot_stages_have_no_foreign_key_to_master_stages(): void
    {
        $columns = \Schema::getColumnListing('workflow_template_snapshot_stages');
        $this->assertNotContains('stage_id', $columns);
    }

    // ── Deleting master template does NOT affect snapshot ─────────────────

    public function test_deleting_template_does_not_delete_snapshot(): void
    {
        $this->seedStages();
        $template = $this->createTemplateWithStages();

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);
        $snapshotId = $snapshot->id;

        $template->stages()->detach();
        $template->delete();

        $this->assertDatabaseHas('workflow_template_snapshots', ['id' => $snapshotId]);
        $this->assertCount(3, WorkflowTemplateSnapshot::find($snapshotId)->stages);
    }

    public function test_deleting_master_stage_does_not_affect_snapshot_stages(): void
    {
        $this->seedStages();
        $template = $this->createTemplateWithStages('T', ['lamaran', 'skrining_cv_hr']);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        Stage::where('key', 'lamaran')->first()->delete();

        $snapshotStages = $snapshot->fresh()->stages;
        $this->assertCount(2, $snapshotStages);
        $this->assertEquals('lamaran', $snapshotStages->first()->key);
    }

    // ── Editing master template does NOT affect snapshot ──────────────────

    public function test_editing_template_name_does_not_change_snapshot(): void
    {
        $this->seedStages();
        $template = $this->createTemplateWithStages('Original Name');

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $template->update(['nama' => 'Changed Name']);

        $this->assertEquals('Original Name', $snapshot->fresh()->nama);
    }

    public function test_editing_master_stage_does_not_change_snapshot_stage(): void
    {
        $this->seedStages();
        $template = $this->createTemplateWithStages('T', ['lamaran']);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        Stage::where('key', 'lamaran')->update(['nama' => 'Application (Renamed)']);

        $this->assertEquals('Lamaran', $snapshot->fresh()->stages->first()->nama);
    }

    public function test_adding_stage_to_template_does_not_affect_existing_snapshot(): void
    {
        $this->seedStages();
        $template = $this->createTemplateWithStages('T', ['lamaran', 'onboarding']);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);
        $this->assertCount(2, $snapshot->stages);

        $extraStage = Stage::where('key', 'tes_kompetensi')->first();
        $template->stages()->attach($extraStage->id, ['position' => 3]);

        $this->assertCount(2, $snapshot->fresh()->stages);
    }

    public function test_removing_stage_from_template_does_not_affect_existing_snapshot(): void
    {
        $this->seedStages();
        $template = $this->createTemplateWithStages('T', ['lamaran', 'skrining_cv_hr', 'onboarding']);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);
        $this->assertCount(3, $snapshot->stages);

        $template->stages()->detach(Stage::where('key', 'skrining_cv_hr')->first()->id);

        $this->assertCount(3, $snapshot->fresh()->stages);
    }

    // ── Vacancy + snapshot lifecycle ─────────────────────────────────────

    public function test_creating_vacancy_creates_snapshot(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $template = $this->createTemplateWithStages('Alur Dokter', ['lamaran', 'skrining_cv_hr', 'onboarding']);

        $this->assertDatabaseCount('workflow_template_snapshots', 0);

        $this->actingAs($admin)->post(route('lowongan.store'), $this->vacancyPayload($unit, $template));

        $this->assertDatabaseCount('workflow_template_snapshots', 1);

        $vacancy = Vacancy::first();
        $snapshot = $vacancy->workflowTemplateSnapshot;
        $this->assertEquals('Alur Dokter', $snapshot->nama);
        $this->assertCount(3, $snapshot->stages);
    }

    public function test_updating_vacancy_with_different_template_creates_new_snapshot(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $templateA = $this->createTemplateWithStages('Template A', ['lamaran', 'onboarding']);
        $templateB = $this->createTemplateWithStages('Template B', ['lamaran', 'skrining_cv_hr', 'onboarding']);

        $this->actingAs($admin)->post(route('lowongan.store'), $this->vacancyPayload($unit, $templateA));
        $vacancy = Vacancy::first();
        $oldSnapshotId = $vacancy->workflow_template_snapshot_id;

        $updatePayload = $this->vacancyPayload($unit, $templateB);
        $updatePayload['status'] = VacancyStatus::Draft->value;
        $this->actingAs($admin)->put(route('lowongan.update', $vacancy), $updatePayload);

        $vacancy->refresh();
        $this->assertNotEquals($oldSnapshotId, $vacancy->workflow_template_snapshot_id);
        $this->assertDatabaseCount('workflow_template_snapshots', 2);
    }

    public function test_old_snapshot_persists_after_vacancy_update_with_new_template(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $templateA = $this->createTemplateWithStages('Template A', ['lamaran', 'onboarding']);
        $templateB = $this->createTemplateWithStages('Template B', ['lamaran', 'skrining_cv_hr', 'onboarding']);

        $this->actingAs($admin)->post(route('lowongan.store'), $this->vacancyPayload($unit, $templateA));
        $vacancy = Vacancy::first();
        $oldSnapshotId = $vacancy->workflow_template_snapshot_id;

        $updatePayload = $this->vacancyPayload($unit, $templateB);
        $updatePayload['status'] = VacancyStatus::Draft->value;
        $this->actingAs($admin)->put(route('lowongan.update', $vacancy), $updatePayload);

        $this->assertDatabaseHas('workflow_template_snapshots', ['id' => $oldSnapshotId]);
        $oldSnapshot = WorkflowTemplateSnapshot::find($oldSnapshotId);
        $this->assertEquals('Template A', $oldSnapshot->nama);
        $this->assertCount(2, $oldSnapshot->stages);
    }

    public function test_deleting_vacancy_does_not_delete_snapshot(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $template = $this->createTemplateWithStages();

        $this->actingAs($admin)->post(route('lowongan.store'), $this->vacancyPayload($unit, $template));
        $vacancy = Vacancy::first();
        $snapshotId = $vacancy->workflow_template_snapshot_id;

        $this->actingAs($admin)->delete(route('lowongan.destroy', $vacancy));

        $this->assertDatabaseMissing('vacancies', ['id' => $vacancy->id]);
        $this->assertDatabaseHas('workflow_template_snapshots', ['id' => $snapshotId]);
    }

    public function test_cannot_delete_snapshot_referenced_by_vacancy(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $template = $this->createTemplateWithStages();

        $this->actingAs($admin)->post(route('lowongan.store'), $this->vacancyPayload($unit, $template));
        $vacancy = Vacancy::first();
        $snapshot = $vacancy->workflowTemplateSnapshot;

        $this->expectException(QueryException::class);
        $snapshot->delete();
    }

    // ── Multiple vacancies / snapshots ────────────────────────────────────

    public function test_two_vacancies_from_same_template_get_independent_snapshots(): void
    {
        $this->seedStages();
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $template = $this->createTemplateWithStages('Shared Template', ['lamaran', 'onboarding']);

        $this->actingAs($admin)->post(route('lowongan.store'), $this->vacancyPayload($unit, $template));
        $payload2 = $this->vacancyPayload($unit, $template);
        $payload2['judul_posisi'] = 'Dokter Umum';
        $this->actingAs($admin)->post(route('lowongan.store'), $payload2);

        $vacancies = Vacancy::all();
        $this->assertCount(2, $vacancies);
        $this->assertNotEquals(
            $vacancies[0]->workflow_template_snapshot_id,
            $vacancies[1]->workflow_template_snapshot_id,
        );
        $this->assertDatabaseCount('workflow_template_snapshots', 2);
    }

    public function test_modifying_template_after_two_snapshots_affects_neither(): void
    {
        $this->seedStages();
        $template = $this->createTemplateWithStages('Original', ['lamaran', 'skrining_cv_hr']);

        $snapshot1 = WorkflowTemplateSnapshot::createFromTemplate($template);
        $snapshot2 = WorkflowTemplateSnapshot::createFromTemplate($template);

        $template->update(['nama' => 'Modified']);
        $template->stages()->detach();

        $this->assertEquals('Original', $snapshot1->fresh()->nama);
        $this->assertEquals('Original', $snapshot2->fresh()->nama);
        $this->assertCount(2, $snapshot1->fresh()->stages);
        $this->assertCount(2, $snapshot2->fresh()->stages);
    }

    // ── Snapshot stage cascade ───────────────────────────────────────────

    public function test_deleting_snapshot_cascades_to_its_stages(): void
    {
        $this->seedStages();
        $template = $this->createTemplateWithStages('T', ['lamaran', 'skrining_cv_hr', 'onboarding']);

        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);
        $snapshotId = $snapshot->id;
        $this->assertDatabaseCount('workflow_template_snapshot_stages', 3);

        $snapshot->delete();

        $this->assertDatabaseMissing('workflow_template_snapshots', ['id' => $snapshotId]);
        $this->assertDatabaseCount('workflow_template_snapshot_stages', 0);
    }
}
