<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitCrudTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ──────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_unit_list(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        Unit::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('unit.index'));

        $response->assertStatus(200);
        $response->assertViewIs('units.index');
    }

    public function test_employee_role_cannot_view_unit_list(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);

        $response = $this->actingAs($user)->get(route('unit.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_view_unit_list(): void
    {
        $response = $this->get(route('unit.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_unit_list_is_searchable(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        Unit::factory()->create(['nama' => 'ICU Dewasa']);
        Unit::factory()->create(['nama' => 'Radiologi']);

        $response = $this->actingAs($admin)->get(route('unit.index', ['q' => 'ICU']));

        $response->assertStatus(200);
        $response->assertSee('ICU Dewasa');
        $response->assertDontSee('Radiologi');
    }

    // ── Create ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_create_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->get(route('unit.create'));

        $response->assertStatus(200);
        $response->assertViewIs('units.create');
    }

    public function test_employee_role_cannot_view_create_form(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);

        $response = $this->actingAs($user)->get(route('unit.create'));

        $response->assertStatus(403);
    }

    public function test_hr_admin_can_create_unit(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('unit.store'), [
            'nama' => 'ICU',
        ]);

        $response->assertRedirect(route('unit.index'));
        $this->assertDatabaseHas('units', ['nama' => 'ICU']);
    }

    public function test_create_unit_fails_when_nama_missing(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('unit.store'), []);

        $response->assertSessionHasErrors(['nama']);
    }

    public function test_create_unit_fails_with_duplicate_nama(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        Unit::factory()->create(['nama' => 'ICU']);

        $response = $this->actingAs($admin)->post(route('unit.store'), ['nama' => 'ICU']);

        $response->assertSessionHasErrors(['nama']);
    }

    public function test_employee_role_cannot_create_unit(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);

        $response = $this->actingAs($user)->post(route('unit.store'), ['nama' => 'ICU']);

        $response->assertStatus(403);
    }

    // ── Edit / Update ──────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_edit_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();

        $response = $this->actingAs($admin)->get(route('unit.edit', $unit));

        $response->assertStatus(200);
        $response->assertViewIs('units.edit');
    }

    public function test_employee_role_cannot_view_edit_form(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);
        $unit = Unit::factory()->create();

        $response = $this->actingAs($user)->get(route('unit.edit', $unit));

        $response->assertStatus(403);
    }

    public function test_hr_admin_can_update_unit(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create(['nama' => 'ICU Lama']);

        $response = $this->actingAs($admin)->put(route('unit.update', $unit), [
            'nama' => 'ICU Baru',
        ]);

        $response->assertRedirect(route('unit.index'));
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'nama' => 'ICU Baru']);
    }

    public function test_update_unit_allows_same_nama_for_same_unit(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create(['nama' => 'ICU']);

        $response = $this->actingAs($admin)->put(route('unit.update', $unit), [
            'nama' => 'ICU',
        ]);

        $response->assertRedirect(route('unit.index'));
    }

    public function test_update_unit_fails_with_duplicate_nama_of_another_unit(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        Unit::factory()->create(['nama' => 'Radiologi']);
        $unit = Unit::factory()->create(['nama' => 'ICU']);

        $response = $this->actingAs($admin)->put(route('unit.update', $unit), [
            'nama' => 'Radiologi',
        ]);

        $response->assertSessionHasErrors(['nama']);
    }

    public function test_employee_role_cannot_update_unit(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);
        $unit = Unit::factory()->create();

        $response = $this->actingAs($user)->put(route('unit.update', $unit), ['nama' => 'Hack']);

        $response->assertStatus(403);
    }

    // ── Delete ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_delete_unit(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();

        $response = $this->actingAs($admin)->delete(route('unit.destroy', $unit));

        $response->assertRedirect(route('unit.index'));
        $this->assertDatabaseMissing('units', ['id' => $unit->id]);
    }

    public function test_hr_admin_cannot_delete_unit_with_vacancies(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        Vacancy::factory()->create(['unit_id' => $unit->id]);

        $response = $this->actingAs($admin)->delete(route('unit.destroy', $unit));

        $response->assertStatus(403);
        $this->assertDatabaseHas('units', ['id' => $unit->id]);
    }

    public function test_employee_role_cannot_delete_unit(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);
        $unit = Unit::factory()->create();

        $response = $this->actingAs($user)->delete(route('unit.destroy', $unit));

        $response->assertStatus(403);
        $this->assertDatabaseHas('units', ['id' => $unit->id]);
    }
}
