<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Employee;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDirectoryTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ──────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_employee_list(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        Employee::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('karyawan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('employees.index');
    }

    public function test_employee_role_cannot_view_employee_list(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);

        $response = $this->actingAs($user)->get(route('karyawan.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_view_employee_list(): void
    {
        $response = $this->get(route('karyawan.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_employee_list_is_searchable(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        Employee::factory()->create(['nama_karyawan' => 'Budi Santoso', 'nip' => '00000001']);
        Employee::factory()->create(['nama_karyawan' => 'Sari Dewi', 'nip' => '00000002']);

        $response = $this->actingAs($admin)->get(route('karyawan.index', ['q' => 'Budi']));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Sari Dewi');
    }

    public function test_employee_list_is_filterable_by_unit(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $icuUnit = Unit::factory()->create(['nama' => 'ICU']);
        $financeUnit = Unit::factory()->create(['nama' => 'Finance']);
        Employee::factory()->create(['unit_id' => $icuUnit->id, 'nip' => '00000001', 'nama_karyawan' => 'Karyawan ICU']);
        Employee::factory()->create(['unit_id' => $financeUnit->id, 'nip' => '00000002', 'nama_karyawan' => 'Karyawan Finance']);

        $response = $this->actingAs($admin)->get(route('karyawan.index', ['unit' => $icuUnit->id]));

        $response->assertStatus(200);
        $response->assertSee('Karyawan ICU');
        $response->assertDontSee('Karyawan Finance');
    }

    public function test_search_combined_with_unit_filter_respects_both_constraints(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $icuUnit = Unit::factory()->create(['nama' => 'ICU']);
        $hrUnit = Unit::factory()->create(['nama' => 'HR']);
        Employee::factory()->create(['nama_karyawan' => 'Ahmad ICU', 'unit_id' => $icuUnit->id, 'nip' => '00000010']);
        Employee::factory()->create(['nama_karyawan' => 'Ahmad HR', 'unit_id' => $hrUnit->id, 'nip' => '00000011']);
        Employee::factory()->create(['nama_karyawan' => 'Budi ICU', 'unit_id' => $icuUnit->id, 'nip' => '00000012']);

        $response = $this->actingAs($admin)->get(route('karyawan.index', ['q' => 'Ahmad', 'unit' => $icuUnit->id]));

        $response->assertStatus(200);
        $response->assertSee('Ahmad ICU');
        $response->assertDontSee('Ahmad HR');
        $response->assertDontSee('Budi ICU');
    }

    // ── Create ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_create_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->get(route('karyawan.create'));

        $response->assertStatus(200);
        $response->assertViewIs('employees.create');
    }

    public function test_employee_role_cannot_view_create_form(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);

        $response = $this->actingAs($user)->get(route('karyawan.create'));

        $response->assertStatus(403);
    }

    public function test_hr_admin_can_create_employee(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();

        $response = $this->actingAs($admin)->post(route('karyawan.store'), [
            'nip' => '12345678',
            'nama_karyawan' => 'Ahmad Fauzi',
            'unit_id' => $unit->id,
            'posisi_pekerjaan' => 'Perawat Primer',
            'profesi' => 'Perawat',
            'jabatan' => 'Staf',
        ]);

        $response->assertRedirect(route('karyawan.index'));
        $this->assertDatabaseHas('employees', [
            'nip' => '12345678',
            'nama_karyawan' => 'Ahmad Fauzi',
        ]);
    }

    public function test_create_employee_fails_with_missing_required_fields(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('karyawan.store'), []);

        $response->assertSessionHasErrors(['nip', 'nama_karyawan', 'unit_id', 'posisi_pekerjaan', 'profesi', 'jabatan']);
    }

    public function test_create_employee_fails_with_duplicate_nip(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        Employee::factory()->create(['nip' => '99999999']);

        $unit = Unit::factory()->create();
        $response = $this->actingAs($admin)->post(route('karyawan.store'), [
            'nip' => '99999999',
            'nama_karyawan' => 'Other Name',
            'unit_id' => $unit->id,
            'posisi_pekerjaan' => 'Staf',
            'profesi' => 'Staf Administrasi',
            'jabatan' => 'Staf',
        ]);

        $response->assertSessionHasErrors(['nip']);
    }

    public function test_employee_role_cannot_create_employee(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);

        $unit = Unit::factory()->create();
        $response = $this->actingAs($user)->post(route('karyawan.store'), [
            'nip' => '11111111',
            'nama_karyawan' => 'Test',
            'unit_id' => $unit->id,
            'posisi_pekerjaan' => 'Test',
            'profesi' => 'Test',
            'jabatan' => 'Test',
        ]);

        $response->assertStatus(403);
    }

    // ── Show ───────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_any_employee_profile(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($admin)->get(route('karyawan.show', $employee));

        $response->assertStatus(200);
        $response->assertViewIs('employees.show');
        $response->assertSee($employee->nama_karyawan);
    }

    public function test_employee_can_view_own_profile(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('karyawan.show', $employee));

        $response->assertStatus(200);
        $response->assertSee($employee->nama_karyawan);
    }

    public function test_employee_cannot_view_other_employee_profile(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);
        $otherEmployee = Employee::factory()->create();

        $response = $this->actingAs($user)->get(route('karyawan.show', $otherEmployee));

        $response->assertStatus(403);
    }

    // ── Edit / Update ──────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_edit_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($admin)->get(route('karyawan.edit', $employee));

        $response->assertStatus(200);
        $response->assertViewIs('employees.edit');
    }

    public function test_employee_role_cannot_view_edit_form(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);
        $employee = Employee::factory()->create();

        $response = $this->actingAs($user)->get(route('karyawan.edit', $employee));

        $response->assertStatus(403);
    }

    public function test_hr_admin_can_update_employee(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $unit = Unit::factory()->create();
        $employee = Employee::factory()->create(['nip' => '10000001']);

        $response = $this->actingAs($admin)->put(route('karyawan.update', $employee), [
            'nip' => '10000001',
            'nama_karyawan' => 'Updated Name',
            'unit_id' => $unit->id,
            'posisi_pekerjaan' => 'Updated Posisi',
            'profesi' => 'Updated Profesi',
            'jabatan' => 'Koordinator',
        ]);

        $response->assertRedirect(route('karyawan.index'));
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'nama_karyawan' => 'Updated Name']);
    }

    public function test_update_employee_fails_with_duplicate_nip_of_another_employee(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        Employee::factory()->create(['nip' => '20000001']);
        $employee = Employee::factory()->create(['nip' => '20000002']);

        $unit = Unit::factory()->create();
        $response = $this->actingAs($admin)->put(route('karyawan.update', $employee), [
            'nip' => '20000001',
            'nama_karyawan' => 'Test',
            'unit_id' => $unit->id,
            'posisi_pekerjaan' => 'Test',
            'profesi' => 'Test',
            'jabatan' => 'Test',
        ]);

        $response->assertSessionHasErrors(['nip']);
    }

    public function test_employee_role_cannot_update_employee(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);
        $employee = Employee::factory()->create(['nip' => '30000001']);

        $unit = Unit::factory()->create();
        $response = $this->actingAs($user)->put(route('karyawan.update', $employee), [
            'nip' => '30000001',
            'nama_karyawan' => 'Hack',
            'unit_id' => $unit->id,
            'posisi_pekerjaan' => 'Test',
            'profesi' => 'Test',
            'jabatan' => 'Test',
        ]);

        $response->assertStatus(403);
    }

    public function test_employee_cannot_edit_own_linked_record(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('karyawan.edit', $employee));

        $response->assertStatus(403);
    }

    public function test_employee_cannot_update_own_linked_record(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('karyawan.update', $employee), [
            'nip' => $employee->nip,
            'nama_karyawan' => 'Self Update',
            'unit_id' => $employee->unit_id,
            'posisi_pekerjaan' => $employee->posisi_pekerjaan,
            'profesi' => $employee->profesi,
            'jabatan' => $employee->jabatan,
        ]);

        $response->assertStatus(403);
    }

    // ── Delete ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_delete_employee(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($admin)->delete(route('karyawan.destroy', $employee));

        $response->assertRedirect(route('karyawan.index'));
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    public function test_employee_role_cannot_delete_employee(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);
        $employee = Employee::factory()->create();

        $response = $this->actingAs($user)->delete(route('karyawan.destroy', $employee));

        $response->assertStatus(403);
        $this->assertDatabaseHas('employees', ['id' => $employee->id]);
    }
}
