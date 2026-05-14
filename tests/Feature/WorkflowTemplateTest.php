<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Stage;
use App\Models\User;
use App\Models\WorkflowTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    private function stageIds(array $keys): array
    {
        $stages = Stage::whereIn('key', $keys)->get()->keyBy('key');

        return collect($keys)
            ->map(fn ($key) => $stages[$key]->id ?? null)
            ->filter()
            ->values()
            ->toArray();
    }

    private function allStageIds(): array
    {
        $keys = [
            'aplikasi', 'formulir_data_pribadi', 'skrining_cv_hr', 'skrining_cv_kepala_unit',
            'email_undangan', 'tes_kompetensi', 'wawancara_kepala_unit', 'wawancara_manajer_hr',
            'wawancara_direktur', 'tes_disc', 'tes_mbti', 'surat_penawaran', 'mcu', 'onboarding',
        ];

        return $this->stageIds($keys);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_template_list(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $template = WorkflowTemplate::factory()->create(['nama' => 'Koordinator']);

        $response = $this->actingAs($admin)->get(route('template-alur.index'));

        $response->assertStatus(200);
        $response->assertViewIs('workflow-templates.index');
        $response->assertSee('Koordinator');
    }

    public function test_index_search_filters_by_name(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        WorkflowTemplate::factory()->create(['nama' => 'Koordinator']);
        WorkflowTemplate::factory()->create(['nama' => 'Staf Medis']);

        $response = $this->actingAs($admin)->get(route('template-alur.index', ['q' => 'koordinator']));

        $response->assertStatus(200);
        $response->assertSee('Koordinator');
        $response->assertDontSee('Staf Medis');
    }

    public function test_non_hr_admin_cannot_view_template_list(): void
    {
        foreach ([Role::HrManager, Role::UnitHead, Role::Director, Role::Employee] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $response = $this->actingAs($user)->get(route('template-alur.index'));
            $response->assertStatus(403);
        }
    }

    public function test_guest_cannot_view_template_list(): void
    {
        $response = $this->get(route('template-alur.index'));
        $response->assertRedirect(route('login'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_create_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();

        $response = $this->actingAs($admin)->get(route('template-alur.create'));

        $response->assertStatus(200);
        $response->assertViewIs('workflow-templates.create');
    }

    public function test_non_hr_admin_cannot_view_create_form(): void
    {
        $user = User::factory()->create(['role' => Role::HrManager]);
        $this->seedStages();

        $response = $this->actingAs($user)->get(route('template-alur.create'));

        $response->assertStatus(403);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_create_template(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $ids = $this->allStageIds();

        $response = $this->actingAs($admin)->post(route('template-alur.store'), [
            'nama' => 'Template Baru',
            'stages' => $ids,
        ]);

        $response->assertRedirect(route('template-alur.index'));
        $this->assertDatabaseHas('workflow_templates', ['nama' => 'Template Baru']);

        $template = WorkflowTemplate::where('nama', 'Template Baru')->first();
        $this->assertCount(14, $template->stages);
    }

    public function test_store_validates_nama_required(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $ids = $this->allStageIds();

        $response = $this->actingAs($admin)->post(route('template-alur.store'), [
            'nama' => '',
            'stages' => $ids,
        ]);

        $response->assertSessionHasErrors('nama');
    }

    public function test_store_validates_stages_required(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();

        $response = $this->actingAs($admin)->post(route('template-alur.store'), [
            'nama' => 'Template',
            'stages' => [],
        ]);

        $response->assertSessionHasErrors('stages');
    }

    public function test_store_rejects_when_first_stage_is_not_aplikasi(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();

        $ids = $this->allStageIds();
        // Swap first and second
        [$ids[0], $ids[1]] = [$ids[1], $ids[0]];

        $response = $this->actingAs($admin)->post(route('template-alur.store'), [
            'nama' => 'Template',
            'stages' => $ids,
        ]);

        $response->assertSessionHasErrors('stages');
        $this->assertDatabaseMissing('workflow_templates', ['nama' => 'Template']);
    }

    public function test_store_rejects_when_last_stage_is_not_onboarding(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();

        $ids = $this->allStageIds();
        // Swap last and second-to-last
        $last = count($ids) - 1;
        [$ids[$last], $ids[$last - 1]] = [$ids[$last - 1], $ids[$last]];

        $response = $this->actingAs($admin)->post(route('template-alur.store'), [
            'nama' => 'Template',
            'stages' => $ids,
        ]);

        $response->assertSessionHasErrors('stages');
    }

    public function test_store_allows_partial_stages_in_middle(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();

        $aplikasiId = Stage::where('key', 'aplikasi')->value('id');
        $onboardingId = Stage::where('key', 'onboarding')->value('id');
        $hrSkriningId = Stage::where('key', 'skrining_cv_hr')->value('id');

        $response = $this->actingAs($admin)->post(route('template-alur.store'), [
            'nama' => 'Template Minimal',
            'stages' => [$aplikasiId, $hrSkriningId, $onboardingId],
        ]);

        $response->assertRedirect(route('template-alur.index'));
        $template = WorkflowTemplate::where('nama', 'Template Minimal')->first();
        $this->assertCount(3, $template->stages);
    }

    public function test_non_hr_admin_cannot_create_template(): void
    {
        $user = User::factory()->create(['role' => Role::HrManager]);
        $this->seedStages();
        $ids = $this->allStageIds();

        $response = $this->actingAs($user)->post(route('template-alur.store'), [
            'nama' => 'Template',
            'stages' => $ids,
        ]);

        $response->assertStatus(403);
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_edit_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $template = WorkflowTemplate::factory()->create();

        $response = $this->actingAs($admin)->get(route('template-alur.edit', $template));

        $response->assertStatus(200);
        $response->assertViewIs('workflow-templates.edit');
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_update_template(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $template = WorkflowTemplate::factory()->create(['nama' => 'Lama']);
        $ids = $this->allStageIds();

        $response = $this->actingAs($admin)->put(route('template-alur.update', $template), [
            'nama' => 'Baru',
            'stages' => $ids,
        ]);

        $response->assertRedirect(route('template-alur.index'));
        $this->assertDatabaseHas('workflow_templates', ['id' => $template->id, 'nama' => 'Baru']);
    }

    public function test_update_persists_stage_order(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $template = WorkflowTemplate::factory()->create();

        $aplikasiId = Stage::where('key', 'aplikasi')->value('id');
        $hrSkriningId = Stage::where('key', 'skrining_cv_hr')->value('id');
        $tesKompetensiId = Stage::where('key', 'tes_kompetensi')->value('id');
        $onboardingId = Stage::where('key', 'onboarding')->value('id');

        $this->actingAs($admin)->put(route('template-alur.update', $template), [
            'nama' => $template->nama,
            'stages' => [$aplikasiId, $tesKompetensiId, $hrSkriningId, $onboardingId],
        ]);

        $stages = $template->fresh()->stages;
        $this->assertEquals($aplikasiId, $stages[0]->id);
        $this->assertEquals($tesKompetensiId, $stages[1]->id);
        $this->assertEquals($hrSkriningId, $stages[2]->id);
        $this->assertEquals($onboardingId, $stages[3]->id);
    }

    public function test_non_hr_admin_cannot_update_template(): void
    {
        $user = User::factory()->create(['role' => Role::UnitHead]);
        $this->seedStages();
        $template = WorkflowTemplate::factory()->create();
        $ids = $this->allStageIds();

        $response = $this->actingAs($user)->put(route('template-alur.update', $template), [
            'nama' => 'Ubah',
            'stages' => $ids,
        ]);

        $response->assertStatus(403);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_hr_admin_can_delete_template(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $template = WorkflowTemplate::factory()->create();

        $response = $this->actingAs($admin)->delete(route('template-alur.destroy', $template));

        $response->assertRedirect(route('template-alur.index'));
        $this->assertDatabaseMissing('workflow_templates', ['id' => $template->id]);
    }

    public function test_non_hr_admin_cannot_delete_template(): void
    {
        $user = User::factory()->create(['role' => Role::Director]);
        $this->seedStages();
        $template = WorkflowTemplate::factory()->create();

        $response = $this->actingAs($user)->delete(route('template-alur.destroy', $template));

        $response->assertStatus(403);
        $this->assertDatabaseHas('workflow_templates', ['id' => $template->id]);
    }
}
