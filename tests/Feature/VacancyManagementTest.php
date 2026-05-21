<?php

namespace Tests\Feature;

use App\Enums\EmploymentType;
use App\Enums\Role;
use App\Enums\VacancyStatus;
use App\Models\Employee;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyManagementTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    private function validPayload(?Unit $unit = null, ?WorkflowTemplate $template = null): array
    {
        $unit ??= Unit::factory()->create();
        $template ??= WorkflowTemplate::factory()->create();

        return [
            'judul_posisi' => 'Perawat ICU',
            'unit_id' => $unit->id,
            'workflow_template_id' => $template->id,
            'jenis_pekerjaan' => EmploymentType::FullTime->value,
            'deskripsi_pekerjaan' => 'Merawat pasien di ICU.',
            'kualifikasi' => 'S1 Keperawatan, pengalaman minimal 1 tahun.',
            'jumlah_posisi' => 2,
            'tenggat_lamaran' => now()->addMonth()->format('Y-m-d'),
            'status' => VacancyStatus::Draft->value,
        ];
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_vacancy_list(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $vacancy = Vacancy::factory()->create(['judul_posisi' => 'Dokter Umum']);

        $response = $this->actingAs($admin)->get(route('lowongan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('vacancies.index');
        $response->assertSee('Dokter Umum');
    }

    public function test_index_filters_by_status(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        Vacancy::factory()->published()->create(['judul_posisi' => 'Perawat']);
        Vacancy::factory()->create(['judul_posisi' => 'Dokter', 'status' => VacancyStatus::Draft]);

        $response = $this->actingAs($admin)->get(route('lowongan.index', ['status' => 'published']));

        $response->assertSee('Perawat');
        $response->assertDontSee('Dokter');
    }

    public function test_index_filters_by_unit(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $unit1 = Unit::factory()->create(['nama' => 'ICU']);
        $unit2 = Unit::factory()->create(['nama' => 'IGD']);
        Vacancy::factory()->create(['judul_posisi' => 'Perawat ICU', 'unit_id' => $unit1->id]);
        Vacancy::factory()->create(['judul_posisi' => 'Perawat IGD', 'unit_id' => $unit2->id]);

        $response = $this->actingAs($admin)->get(route('lowongan.index', ['unit_id' => $unit1->id]));

        $response->assertSee('Perawat ICU');
        $response->assertDontSee('Perawat IGD');
    }

    public function test_index_searches_by_title(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        Vacancy::factory()->create(['judul_posisi' => 'Perawat ICU']);
        Vacancy::factory()->create(['judul_posisi' => 'Dokter Umum']);

        $response = $this->actingAs($admin)->get(route('lowongan.index', ['q' => 'perawat']));

        $response->assertSee('Perawat ICU');
        $response->assertDontSee('Dokter Umum');
    }

    public function test_hr_manager_can_view_vacancy_list(): void
    {
        $user = User::factory()->create(['role' => Role::HrManager]);
        $this->seedStages();
        Vacancy::factory()->create(['judul_posisi' => 'Dokter Umum']);

        $response = $this->actingAs($user)->get(route('lowongan.index'));

        $response->assertStatus(200);
        $response->assertSee('Dokter Umum');
    }

    public function test_director_can_view_vacancy_list(): void
    {
        $user = User::factory()->create(['role' => Role::Director]);
        $this->seedStages();
        Vacancy::factory()->create(['judul_posisi' => 'Dokter Umum']);

        $response = $this->actingAs($user)->get(route('lowongan.index'));

        $response->assertStatus(200);
        $response->assertSee('Dokter Umum');
    }

    public function test_unit_head_with_employee_can_view_vacancy_list_scoped_to_own_unit(): void
    {
        $this->seedStages();
        $ownUnit = Unit::factory()->create(['nama' => 'ICU']);
        $otherUnit = Unit::factory()->create(['nama' => 'IGD']);

        $user = User::factory()->create(['role' => Role::UnitHead]);
        Employee::factory()->create(['user_id' => $user->id, 'unit_id' => $ownUnit->id]);

        Vacancy::factory()->create(['judul_posisi' => 'Perawat ICU', 'unit_id' => $ownUnit->id]);
        Vacancy::factory()->create(['judul_posisi' => 'Perawat IGD', 'unit_id' => $otherUnit->id]);

        $response = $this->actingAs($user)->get(route('lowongan.index'));

        $response->assertStatus(200);
        $response->assertSee('Perawat ICU');
        $response->assertDontSee('Perawat IGD');
    }

    public function test_unit_head_without_employee_cannot_view_vacancy_list(): void
    {
        $user = User::factory()->create(['role' => Role::UnitHead]);

        $response = $this->actingAs($user)->get(route('lowongan.index'));

        $response->assertStatus(403);
    }

    public function test_unit_head_with_valid_unit_sees_no_warning(): void
    {
        $this->seedStages();
        $unit = Unit::factory()->create();
        $user = User::factory()->create(['role' => Role::UnitHead]);
        Employee::factory()->create(['user_id' => $user->id, 'unit_id' => $unit->id]);

        $response = $this->actingAs($user)->get(route('lowongan.index'));

        $response->assertStatus(200);
        $response->assertSessionMissing('warning');
    }

    public function test_unit_head_cannot_escape_scope_via_unit_id_param(): void
    {
        $this->seedStages();
        $ownUnit = Unit::factory()->create(['nama' => 'ICU']);
        $otherUnit = Unit::factory()->create(['nama' => 'IGD']);

        $user = User::factory()->create(['role' => Role::UnitHead]);
        Employee::factory()->create(['user_id' => $user->id, 'unit_id' => $ownUnit->id]);

        Vacancy::factory()->create(['judul_posisi' => 'Perawat IGD', 'unit_id' => $otherUnit->id]);

        $response = $this->actingAs($user)->get(route('lowongan.index', ['unit_id' => $otherUnit->id]));

        $response->assertStatus(200);
        $response->assertDontSee('Perawat IGD');
    }

    public function test_employee_cannot_view_vacancy_list(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);

        $response = $this->actingAs($user)->get(route('lowongan.index'));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_from_vacancy_list(): void
    {
        $response = $this->get(route('lowongan.index'));
        $response->assertRedirect(route('login'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_create_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();

        $response = $this->actingAs($admin)->get(route('lowongan.create'));

        $response->assertStatus(200);
        $response->assertViewIs('vacancies.create');
    }

    public function test_non_hr_admin_cannot_view_create_form(): void
    {
        $user = User::factory()->create(['role' => Role::HrManager]);

        $response = $this->actingAs($user)->get(route('lowongan.create'));

        $response->assertStatus(403);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_create_vacancy(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $payload = $this->validPayload();

        $response = $this->actingAs($admin)->post(route('lowongan.store'), $payload);

        $response->assertRedirect(route('lowongan.index'));
        $this->assertDatabaseHas('vacancies', [
            'judul_posisi' => 'Perawat ICU',
            'status' => VacancyStatus::Draft->value,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('lowongan.store'), []);

        $response->assertSessionHasErrors([
            'judul_posisi', 'unit_id', 'workflow_template_id',
            'jenis_pekerjaan', 'deskripsi_pekerjaan', 'kualifikasi',
            'jumlah_posisi', 'tenggat_lamaran',
        ]);
    }

    public function test_store_rejects_past_deadline(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $payload = $this->validPayload();
        $payload['tenggat_lamaran'] = now()->subDay()->format('Y-m-d');

        $response = $this->actingAs($admin)->post(route('lowongan.store'), $payload);

        $response->assertSessionHasErrors('tenggat_lamaran');
    }

    public function test_non_hr_admin_cannot_create_vacancy(): void
    {
        $user = User::factory()->create(['role' => Role::HrManager]);
        $this->seedStages();
        $payload = $this->validPayload();

        $response = $this->actingAs($user)->post(route('lowongan.store'), $payload);

        $response->assertStatus(403);
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_edit_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $vacancy = Vacancy::factory()->create();

        $response = $this->actingAs($admin)->get(route('lowongan.edit', $vacancy));

        $response->assertStatus(200);
        $response->assertViewIs('vacancies.edit');
    }

    public function test_hr_admin_can_update_vacancy(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $vacancy = Vacancy::factory()->create();
        $payload = $this->validPayload();
        $payload['judul_posisi'] = 'Bidan Senior';
        $payload['status'] = VacancyStatus::Published->value;

        $response = $this->actingAs($admin)->put(route('lowongan.update', $vacancy), $payload);

        $response->assertRedirect(route('lowongan.index'));
        $this->assertDatabaseHas('vacancies', [
            'id' => $vacancy->id,
            'judul_posisi' => 'Bidan Senior',
            'status' => VacancyStatus::Published->value,
        ]);
    }

    public function test_status_transition_draft_to_published(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $vacancy = Vacancy::factory()->create(['status' => VacancyStatus::Draft]);
        $payload = $this->validPayload($vacancy->unit, WorkflowTemplate::factory()->create());
        $payload['status'] = VacancyStatus::Published->value;

        $this->actingAs($admin)->put(route('lowongan.update', $vacancy), $payload);

        $this->assertEquals(VacancyStatus::Published, $vacancy->fresh()->status);
    }

    public function test_status_transition_published_to_closed(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $vacancy = Vacancy::factory()->published()->create();
        $payload = $this->validPayload($vacancy->unit, WorkflowTemplate::factory()->create());
        $payload['status'] = VacancyStatus::Closed->value;

        $this->actingAs($admin)->put(route('lowongan.update', $vacancy), $payload);

        $this->assertEquals(VacancyStatus::Closed, $vacancy->fresh()->status);
    }

    public function test_non_hr_admin_cannot_update_vacancy(): void
    {
        $user = User::factory()->create(['role' => Role::UnitHead]);
        $this->seedStages();
        $vacancy = Vacancy::factory()->create();
        $payload = $this->validPayload();

        $response = $this->actingAs($user)->put(route('lowongan.update', $vacancy), $payload);

        $response->assertStatus(403);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_hr_admin_can_delete_vacancy(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->seedStages();
        $vacancy = Vacancy::factory()->create();

        $response = $this->actingAs($admin)->delete(route('lowongan.destroy', $vacancy));

        $response->assertRedirect(route('lowongan.index'));
        $this->assertDatabaseMissing('vacancies', ['id' => $vacancy->id]);
    }

    public function test_non_hr_admin_cannot_delete_vacancy(): void
    {
        $user = User::factory()->create(['role' => Role::Director]);
        $this->seedStages();
        $vacancy = Vacancy::factory()->create();

        $response = $this->actingAs($user)->delete(route('lowongan.destroy', $vacancy));

        $response->assertStatus(403);
        $this->assertDatabaseHas('vacancies', ['id' => $vacancy->id]);
    }
}
