<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function makeTemplate(array $overrides = []): EmailTemplate
    {
        return EmailTemplate::create(array_merge([
            'key' => 'test_template',
            'deskripsi' => 'Template untuk pengujian',
            'subjek' => 'Subjek Test {nama_kandidat}',
            'isi' => 'Halo {nama_kandidat}, ini adalah isi email.',
        ], $overrides));
    }

    // ── Index ──────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_template_list(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->makeTemplate();

        $response = $this->actingAs($admin)->get(route('template-email.index'));

        $response->assertStatus(200);
        $response->assertViewIs('email-templates.index');
    }

    public function test_non_hr_admin_cannot_view_template_list(): void
    {
        $user = User::factory()->create(['role' => Role::HrManager]);

        $response = $this->actingAs($user)->get(route('template-email.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_view_template_list(): void
    {
        $response = $this->get(route('template-email.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_template_list_shows_all_templates(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->makeTemplate(['key' => 'lamaran_diterima', 'deskripsi' => 'Lamaran diterima']);
        $this->makeTemplate(['key' => 'kandidat_ditolak', 'deskripsi' => 'Kandidat ditolak']);

        $response = $this->actingAs($admin)->get(route('template-email.index'));

        $response->assertSee('lamaran_diterima');
        $response->assertSee('kandidat_ditolak');
    }

    // ── Edit ───────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_edit_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = $this->makeTemplate();

        $response = $this->actingAs($admin)->get(route('template-email.edit', $template));

        $response->assertStatus(200);
        $response->assertViewIs('email-templates.edit');
        $response->assertViewHas('templateEmail', $template);
    }

    public function test_non_hr_admin_cannot_view_edit_form(): void
    {
        $user = User::factory()->create(['role' => Role::UnitHead]);
        $template = $this->makeTemplate();

        $response = $this->actingAs($user)->get(route('template-email.edit', $template));

        $response->assertStatus(403);
    }

    // ── Update ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_update_template(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = $this->makeTemplate();

        $response = $this->actingAs($admin)->put(route('template-email.update', $template), [
            'subjek' => 'Subjek baru {nama_kandidat}',
            'isi' => 'Isi email yang baru untuk {nama_kandidat}.',
        ]);

        $response->assertRedirect(route('template-email.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'subjek' => 'Subjek baru {nama_kandidat}',
            'isi' => 'Isi email yang baru untuk {nama_kandidat}.',
        ]);
    }

    public function test_non_hr_admin_cannot_update_template(): void
    {
        $user = User::factory()->create(['role' => Role::Director]);
        $template = $this->makeTemplate();

        $response = $this->actingAs($user)->put(route('template-email.update', $template), [
            'subjek' => 'Diubah',
            'isi' => 'Diubah',
        ]);

        $response->assertStatus(403);
    }

    public function test_update_requires_subjek(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = $this->makeTemplate();

        $response = $this->actingAs($admin)->put(route('template-email.update', $template), [
            'subjek' => '',
            'isi' => 'Isi valid',
        ]);

        $response->assertSessionHasErrors('subjek');
    }

    public function test_update_requires_isi(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = $this->makeTemplate();

        $response = $this->actingAs($admin)->put(route('template-email.update', $template), [
            'subjek' => 'Subjek valid',
            'isi' => '',
        ]);

        $response->assertSessionHasErrors('isi');
    }

    public function test_update_cannot_change_key(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = $this->makeTemplate(['key' => 'original_key']);

        $this->actingAs($admin)->put(route('template-email.update', $template), [
            'key' => 'changed_key',
            'subjek' => 'Subjek',
            'isi' => 'Isi',
        ]);

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'key' => 'original_key',
        ]);
    }
}
