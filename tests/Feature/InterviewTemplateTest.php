<?php

namespace Tests\Feature;

use App\Enums\InterviewTemplateType;
use App\Enums\Role;
use App\Models\InterviewTemplate;
use App\Models\InterviewTemplateItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewTemplateTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_template_list(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = InterviewTemplate::factory()->kriteriaPenilaian()->create(['nama' => 'Kriteria Umum']);

        $response = $this->actingAs($admin)->get(route('template-wawancara.index'));

        $response->assertStatus(200);
        $response->assertViewIs('interview-templates.index');
        $response->assertSee('Kriteria Umum');
    }

    public function test_index_search_filters_by_name(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        InterviewTemplate::factory()->create(['nama' => 'Kriteria Umum']);
        InterviewTemplate::factory()->create(['nama' => 'Kesiapan Perawat']);

        $response = $this->actingAs($admin)->get(route('template-wawancara.index', ['q' => 'kriteria']));

        $response->assertStatus(200);
        $response->assertSee('Kriteria Umum');
        $response->assertDontSee('Kesiapan Perawat');
    }

    public function test_index_shows_item_count(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = InterviewTemplate::factory()->create();
        InterviewTemplateItem::factory()->count(3)->create(['interview_template_id' => $template->id]);

        $response = $this->actingAs($admin)->get(route('template-wawancara.index'));

        $response->assertStatus(200);
        $response->assertSee('3 item');
    }

    public function test_non_hr_admin_cannot_view_template_list(): void
    {
        foreach ([Role::HrManager, Role::UnitHead, Role::Director, Role::Employee] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $response = $this->actingAs($user)->get(route('template-wawancara.index'));
            $response->assertStatus(403);
        }
    }

    public function test_guest_cannot_view_template_list(): void
    {
        $response = $this->get(route('template-wawancara.index'));
        $response->assertRedirect(route('login'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_create_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->get(route('template-wawancara.create'));

        $response->assertStatus(200);
        $response->assertViewIs('interview-templates.create');
    }

    public function test_non_hr_admin_cannot_view_create_form(): void
    {
        $user = User::factory()->create(['role' => Role::HrManager]);

        $response = $this->actingAs($user)->get(route('template-wawancara.create'));

        $response->assertStatus(403);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_create_kriteria_penilaian_template(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('template-wawancara.store'), [
            'nama' => 'Kriteria Kepala Unit',
            'tipe' => 'kriteria_penilaian',
            'items' => [
                ['teks' => 'Pengetahuan Teknis'],
                ['teks' => 'Pengalaman Relevan'],
                ['teks' => 'Komunikasi'],
            ],
        ]);

        $response->assertRedirect(route('template-wawancara.index'));
        $this->assertDatabaseHas('interview_templates', [
            'nama' => 'Kriteria Kepala Unit',
            'tipe' => 'kriteria_penilaian',
        ]);

        $template = InterviewTemplate::where('nama', 'Kriteria Kepala Unit')->first();
        $this->assertCount(3, $template->items);
        $this->assertEquals(1, $template->items->first()->urutan);
        $this->assertEquals(3, $template->items->last()->urutan);
    }

    public function test_hr_admin_can_create_kesiapan_template(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('template-wawancara.store'), [
            'nama' => 'Kesiapan Perawat',
            'tipe' => 'kesiapan',
            'items' => [
                ['teks' => 'Bersedia bekerja shift?'],
                ['teks' => 'Bersedia ditempatkan di unit mana saja?'],
            ],
        ]);

        $response->assertRedirect(route('template-wawancara.index'));
        $this->assertDatabaseHas('interview_templates', [
            'nama' => 'Kesiapan Perawat',
            'tipe' => 'kesiapan',
        ]);
    }

    public function test_store_validates_nama_required(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('template-wawancara.store'), [
            'nama' => '',
            'tipe' => 'kriteria_penilaian',
            'items' => [['teks' => 'Item']],
        ]);

        $response->assertSessionHasErrors('nama');
    }

    public function test_store_validates_nama_unique(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        InterviewTemplate::factory()->create(['nama' => 'Existing']);

        $response = $this->actingAs($admin)->post(route('template-wawancara.store'), [
            'nama' => 'Existing',
            'tipe' => 'kriteria_penilaian',
            'items' => [['teks' => 'Item']],
        ]);

        $response->assertSessionHasErrors('nama');
    }

    public function test_store_validates_tipe_must_be_valid_enum(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('template-wawancara.store'), [
            'nama' => 'Template',
            'tipe' => 'invalid_type',
            'items' => [['teks' => 'Item']],
        ]);

        $response->assertSessionHasErrors('tipe');
    }

    public function test_store_validates_items_required(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('template-wawancara.store'), [
            'nama' => 'Template',
            'tipe' => 'kriteria_penilaian',
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_store_validates_item_teks_required(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->post(route('template-wawancara.store'), [
            'nama' => 'Template',
            'tipe' => 'kriteria_penilaian',
            'items' => [['teks' => '']],
        ]);

        $response->assertSessionHasErrors('items.0.teks');
    }

    public function test_non_hr_admin_cannot_create_template(): void
    {
        $user = User::factory()->create(['role' => Role::HrManager]);

        $response = $this->actingAs($user)->post(route('template-wawancara.store'), [
            'nama' => 'Template',
            'tipe' => 'kriteria_penilaian',
            'items' => [['teks' => 'Item']],
        ]);

        $response->assertStatus(403);
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_edit_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = InterviewTemplate::factory()->create();

        $response = $this->actingAs($admin)->get(route('template-wawancara.edit', $template));

        $response->assertStatus(200);
        $response->assertViewIs('interview-templates.edit');
    }

    public function test_non_hr_admin_cannot_view_edit_form(): void
    {
        $user = User::factory()->create(['role' => Role::UnitHead]);
        $template = InterviewTemplate::factory()->create();

        $response = $this->actingAs($user)->get(route('template-wawancara.edit', $template));

        $response->assertStatus(403);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_update_template_name_and_items(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = InterviewTemplate::factory()->kriteriaPenilaian()->create(['nama' => 'Lama']);
        $existingItem = InterviewTemplateItem::factory()->create([
            'interview_template_id' => $template->id,
            'teks' => 'Existing Item',
            'urutan' => 1,
        ]);

        $response = $this->actingAs($admin)->put(route('template-wawancara.update', $template), [
            'nama' => 'Baru',
            'items' => [
                ['id' => $existingItem->id, 'teks' => 'Updated Item'],
                ['teks' => 'New Item'],
            ],
        ]);

        $response->assertRedirect(route('template-wawancara.index'));
        $this->assertDatabaseHas('interview_templates', ['id' => $template->id, 'nama' => 'Baru']);
        $this->assertDatabaseHas('interview_template_items', ['id' => $existingItem->id, 'teks' => 'Updated Item', 'urutan' => 1]);
        $this->assertDatabaseHas('interview_template_items', ['teks' => 'New Item', 'urutan' => 2]);
    }

    public function test_update_removes_deleted_items(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = InterviewTemplate::factory()->create();
        $keepItem = InterviewTemplateItem::factory()->create(['interview_template_id' => $template->id, 'urutan' => 1]);
        $removeItem = InterviewTemplateItem::factory()->create(['interview_template_id' => $template->id, 'urutan' => 2]);

        $this->actingAs($admin)->put(route('template-wawancara.update', $template), [
            'nama' => $template->nama,
            'items' => [
                ['id' => $keepItem->id, 'teks' => $keepItem->teks],
            ],
        ]);

        $this->assertDatabaseHas('interview_template_items', ['id' => $keepItem->id]);
        $this->assertDatabaseMissing('interview_template_items', ['id' => $removeItem->id]);
    }

    public function test_update_does_not_change_tipe_even_if_submitted(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = InterviewTemplate::factory()->kriteriaPenilaian()->create();
        InterviewTemplateItem::factory()->create(['interview_template_id' => $template->id]);

        $this->actingAs($admin)->put(route('template-wawancara.update', $template), [
            'nama' => $template->nama,
            'tipe' => 'kesiapan',
            'items' => [['teks' => 'Item']],
        ]);

        $this->assertEquals(InterviewTemplateType::KriteriaPenilaian, $template->fresh()->tipe);
    }

    public function test_non_hr_admin_cannot_update_template(): void
    {
        $user = User::factory()->create(['role' => Role::Director]);
        $template = InterviewTemplate::factory()->create();

        $response = $this->actingAs($user)->put(route('template-wawancara.update', $template), [
            'nama' => 'Changed',
            'items' => [['teks' => 'Item']],
        ]);

        $response->assertStatus(403);
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function test_hr_admin_can_delete_template(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = InterviewTemplate::factory()->create();

        $response = $this->actingAs($admin)->delete(route('template-wawancara.destroy', $template));

        $response->assertRedirect(route('template-wawancara.index'));
        $this->assertDatabaseMissing('interview_templates', ['id' => $template->id]);
    }

    public function test_destroy_cascades_to_items(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $template = InterviewTemplate::factory()->create();
        $item = InterviewTemplateItem::factory()->create(['interview_template_id' => $template->id]);

        $this->actingAs($admin)->delete(route('template-wawancara.destroy', $template));

        $this->assertDatabaseMissing('interview_templates', ['id' => $template->id]);
        $this->assertDatabaseMissing('interview_template_items', ['id' => $item->id]);
    }

    public function test_non_hr_admin_cannot_delete_template(): void
    {
        $user = User::factory()->create(['role' => Role::Employee]);
        $template = InterviewTemplate::factory()->create();

        $response = $this->actingAs($user)->delete(route('template-wawancara.destroy', $template));

        $response->assertStatus(403);
        $this->assertDatabaseHas('interview_templates', ['id' => $template->id]);
    }
}
