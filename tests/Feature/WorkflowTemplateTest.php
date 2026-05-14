<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use App\Models\WorkflowStage;
use App\Models\WorkflowTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'WorkflowStageSeeder']);
    }

    private function hrAdmin(): User
    {
        return User::factory()->hrAdmin()->create();
    }

    private function stageIds(array $keys): array
    {
        return WorkflowStage::whereIn('key', $keys)->orderByRaw(
            'CASE key '.implode(' ', array_map(fn ($k, $i) => "WHEN '$k' THEN $i", $keys, array_keys($keys))).' END'
        )->pluck('id')->toArray();
    }

    private function defaultStageIds(): array
    {
        return WorkflowStage::orderBy('default_order')->pluck('id')->toArray();
    }

    // ── Index ──────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_template_list(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        WorkflowTemplate::factory()->create(['name' => 'Test Template']);

        $response = $this->actingAs($admin)->get(route('alur-rekrutmen.index'));

        $response->assertStatus(200);
        $response->assertViewIs('alur-rekrutmen.index');
    }

    public function test_non_hr_admin_cannot_view_template_list(): void
    {
        foreach ([Role::HrManager, Role::UnitHead, Role::Director, Role::Employee] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $response = $this->actingAs($user)->get(route('alur-rekrutmen.index'));
            $response->assertStatus(403);
        }
    }

    public function test_guest_cannot_view_template_list(): void
    {
        $response = $this->get(route('alur-rekrutmen.index'));
        $response->assertRedirect(route('login'));
    }

    // ── Create ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_create_form(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();

        $response = $this->actingAs($admin)->get(route('alur-rekrutmen.create'));

        $response->assertStatus(200);
        $response->assertViewIs('alur-rekrutmen.create');
    }

    // ── Store ──────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_create_template(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $stageIds = $this->defaultStageIds();

        $response = $this->actingAs($admin)->post(route('alur-rekrutmen.store'), [
            'name' => 'Template Baru',
            'description' => 'Deskripsi template',
            'stage_ids' => $stageIds,
        ]);

        $response->assertRedirect(route('alur-rekrutmen.index'));
        $this->assertDatabaseHas('workflow_templates', ['name' => 'Template Baru']);

        $template = WorkflowTemplate::where('name', 'Template Baru')->first();
        $this->assertEquals(count($stageIds), $template->stages()->count());
    }

    public function test_create_template_requires_name(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();

        $response = $this->actingAs($admin)->post(route('alur-rekrutmen.store'), [
            'stage_ids' => $this->defaultStageIds(),
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_create_template_requires_unique_name(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        WorkflowTemplate::factory()->create(['name' => 'Duplikat']);

        $response = $this->actingAs($admin)->post(route('alur-rekrutmen.store'), [
            'name' => 'Duplikat',
            'stage_ids' => $this->defaultStageIds(),
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_create_template_requires_at_least_two_stages(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $firstStageId = WorkflowStage::where('is_locked_first', true)->value('id');

        $response = $this->actingAs($admin)->post(route('alur-rekrutmen.store'), [
            'name' => 'Single Stage',
            'stage_ids' => [$firstStageId],
        ]);

        $response->assertSessionHasErrors('stage_ids');
    }

    // ── Constraint enforcement ─────────────────────────────────────────────────

    public function test_application_must_be_first_stage(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();

        $allIds = $this->defaultStageIds();
        // Put application (first) stage at second position
        $first = array_shift($allIds);
        array_splice($allIds, 1, 0, [$first]);

        $response = $this->actingAs($admin)->post(route('alur-rekrutmen.store'), [
            'name' => 'Wrong Order',
            'stage_ids' => $allIds,
        ]);

        $response->assertSessionHasErrors('stage_ids');
    }

    public function test_onboarding_must_be_last_stage(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();

        $allIds = $this->defaultStageIds();
        // Put onboarding (last) stage at second-to-last position
        $last = array_pop($allIds);
        array_splice($allIds, count($allIds) - 1, 0, [$last]);

        $response = $this->actingAs($admin)->post(route('alur-rekrutmen.store'), [
            'name' => 'Wrong Order',
            'stage_ids' => $allIds,
        ]);

        $response->assertSessionHasErrors('stage_ids');
    }

    public function test_template_can_be_created_without_optional_middle_stages(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();

        $firstId = WorkflowStage::where('is_locked_first', true)->value('id');
        $lastId = WorkflowStage::where('is_locked_last', true)->value('id');

        $response = $this->actingAs($admin)->post(route('alur-rekrutmen.store'), [
            'name' => 'Minimal Template',
            'stage_ids' => [$firstId, $lastId],
        ]);

        $response->assertRedirect(route('alur-rekrutmen.index'));
        $this->assertDatabaseHas('workflow_templates', ['name' => 'Minimal Template']);
    }

    // ── Show ───────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_template(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $template = WorkflowTemplate::factory()->withDefaultStages()->create();

        $response = $this->actingAs($admin)->get(route('alur-rekrutmen.show', $template));

        $response->assertStatus(200);
        $response->assertViewIs('alur-rekrutmen.show');
        $response->assertSee($template->name);
    }

    // ── Edit / Update ──────────────────────────────────────────────────────────

    public function test_hr_admin_can_update_template(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $template = WorkflowTemplate::factory()->withDefaultStages()->create(['name' => 'Lama']);

        $stageIds = $this->defaultStageIds();

        $response = $this->actingAs($admin)->put(route('alur-rekrutmen.update', $template), [
            'name' => 'Baru',
            'description' => 'Updated',
            'stage_ids' => $stageIds,
        ]);

        $response->assertRedirect(route('alur-rekrutmen.index'));
        $this->assertDatabaseHas('workflow_templates', ['name' => 'Baru']);
    }

    public function test_update_allows_same_name_on_same_template(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $template = WorkflowTemplate::factory()->withDefaultStages()->create(['name' => 'Koordinator']);

        $response = $this->actingAs($admin)->put(route('alur-rekrutmen.update', $template), [
            'name' => 'Koordinator',
            'stage_ids' => $this->defaultStageIds(),
        ]);

        $response->assertRedirect(route('alur-rekrutmen.index'));
    }

    // ── Delete ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_delete_template(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $template = WorkflowTemplate::factory()->withDefaultStages()->create();

        $response = $this->actingAs($admin)->delete(route('alur-rekrutmen.destroy', $template));

        $response->assertRedirect(route('alur-rekrutmen.index'));
        $this->assertDatabaseMissing('workflow_templates', ['id' => $template->id]);
    }

    public function test_non_hr_admin_cannot_delete_template(): void
    {
        $this->seedStages();
        $template = WorkflowTemplate::factory()->withDefaultStages()->create();

        foreach ([Role::HrManager, Role::UnitHead, Role::Director, Role::Employee] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $response = $this->actingAs($user)->delete(route('alur-rekrutmen.destroy', $template));
            $response->assertStatus(403);
        }
    }

    // ── Reordering ─────────────────────────────────────────────────────────────

    public function test_stage_positions_are_saved_in_order(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();

        $firstId = WorkflowStage::where('is_locked_first', true)->value('id');
        $lastId = WorkflowStage::where('is_locked_last', true)->value('id');
        $middleStage = WorkflowStage::where('is_locked_first', false)
            ->where('is_locked_last', false)
            ->orderBy('default_order')
            ->first();

        $stageIds = [$firstId, $middleStage->id, $lastId];

        $this->actingAs($admin)->post(route('alur-rekrutmen.store'), [
            'name' => 'Custom Order',
            'stage_ids' => $stageIds,
        ]);

        $template = WorkflowTemplate::where('name', 'Custom Order')->first();
        $savedStages = $template->stages()->get();

        $this->assertEquals($firstId, $savedStages[0]->id);
        $this->assertEquals($middleStage->id, $savedStages[1]->id);
        $this->assertEquals($lastId, $savedStages[2]->id);
    }
}
