<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TalentPoolTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Candidate with a single Reserved application — the precondition for flagging.
     */
    private function reservedCandidate(): Candidate
    {
        $candidate = Candidate::factory()->create();
        $application = Application::factory()->create(['candidate_id' => $candidate->id]);
        ApplicationStage::factory()->reserved()->create(['application_id' => $application->id]);

        return $candidate;
    }

    public function test_pipeline_manager_can_flag_candidate_with_reason(): void
    {
        $user = User::factory()->withRole(Role::HrAdmin)->create();
        $candidate = $this->reservedCandidate();

        $response = $this->actingAs($user)->post(route('kandidat-potensial.store', $candidate), [
            'alasan' => 'Kualifikasi kuat, cocok untuk posisi serupa.',
        ]);

        $response->assertRedirect();
        $candidate->refresh();
        $this->assertTrue($candidate->isInTalentPool());
        $this->assertSame('Kualifikasi kuat, cocok untuk posisi serupa.', $candidate->talent_pool_reason);
        $this->assertSame($user->id, $candidate->talent_pool_flagged_by);
        $this->assertNotNull($candidate->talent_pool_flagged_at);
    }

    public function test_flagging_requires_a_reason(): void
    {
        $user = User::factory()->withRole(Role::HrAdmin)->create();
        $candidate = $this->reservedCandidate();

        $response = $this->actingAs($user)->post(route('kandidat-potensial.store', $candidate), [
            'alasan' => '',
        ]);

        $response->assertSessionHasErrors('alasan');
        $this->assertFalse($candidate->fresh()->isInTalentPool());
    }

    public function test_cannot_flag_candidate_without_reserved_application(): void
    {
        $user = User::factory()->withRole(Role::HrAdmin)->create();
        $candidate = Candidate::factory()->create();

        $response = $this->actingAs($user)->post(route('kandidat-potensial.store', $candidate), [
            'alasan' => 'Kualifikasi kuat.',
        ]);

        $response->assertForbidden();
        $this->assertFalse($candidate->fresh()->isInTalentPool());
    }

    public function test_flagging_does_not_overwrite_existing_flag(): void
    {
        $flagger = User::factory()->withRole(Role::HrAdmin)->create();
        $other = User::factory()->withRole(Role::HrManager)->create();

        $candidate = $this->reservedCandidate();
        $candidate->update([
            'talent_pool_flagged_at' => now()->subDay(),
            'talent_pool_flagged_by' => $flagger->id,
            'talent_pool_reason' => 'Alasan asli.',
        ]);
        $original = $candidate->fresh();

        $response = $this->actingAs($other)->post(route('kandidat-potensial.store', $candidate), [
            'alasan' => 'Alasan baru yang berbeda.',
        ]);

        $response->assertRedirect();
        $candidate->refresh();
        $this->assertSame('Alasan asli.', $candidate->talent_pool_reason);
        $this->assertSame($flagger->id, $candidate->talent_pool_flagged_by);
        $this->assertEquals($original->talent_pool_flagged_at, $candidate->talent_pool_flagged_at);
    }

    public function test_employee_cannot_flag_candidate(): void
    {
        $user = User::factory()->withRole(Role::Employee)->create();
        $candidate = Candidate::factory()->create();

        $response = $this->actingAs($user)->post(route('kandidat-potensial.store', $candidate), [
            'alasan' => 'Bagus.',
        ]);

        $response->assertForbidden();
        $this->assertFalse($candidate->fresh()->isInTalentPool());
    }

    public function test_pipeline_manager_can_unflag_candidate(): void
    {
        $user = User::factory()->withRole(Role::HrAdmin)->create();
        $candidate = Candidate::factory()->create([
            'talent_pool_flagged_at' => now(),
            'talent_pool_flagged_by' => $user->id,
            'talent_pool_reason' => 'Sebelumnya ditandai.',
        ]);

        $response = $this->actingAs($user)->delete(route('kandidat-potensial.destroy', $candidate));

        $response->assertRedirect();
        $candidate->refresh();
        $this->assertFalse($candidate->isInTalentPool());
        $this->assertNull($candidate->talent_pool_flagged_by);
        $this->assertNull($candidate->talent_pool_reason);
    }

    public function test_hr_can_view_talent_pool_with_only_flagged_candidates(): void
    {
        $user = User::factory()->withRole(Role::HrManager)->create();
        $flagged = Candidate::factory()->create([
            'nama_lengkap' => 'Budi Flagged',
            'talent_pool_flagged_at' => now(),
            'talent_pool_flagged_by' => $user->id,
            'talent_pool_reason' => 'Potensial.',
        ]);
        $notFlagged = Candidate::factory()->create(['nama_lengkap' => 'Siti Biasa']);

        $response = $this->actingAs($user)->get(route('kandidat-potensial.index'));

        $response->assertOk();
        $response->assertViewIs('talent-pool.index');
        $response->assertSee('Budi Flagged');
        $response->assertDontSee('Siti Biasa');
    }

    public function test_search_does_not_leak_unflagged_candidates_by_email(): void
    {
        if (\DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Search uses PostgreSQL ilike; not portable to the sqlite test driver.');
        }

        $user = User::factory()->withRole(Role::HrManager)->create();
        $flagged = Candidate::factory()->create([
            'nama_lengkap' => 'Budi Flagged',
            'email' => 'budi@example.com',
            'talent_pool_flagged_at' => now(),
            'talent_pool_flagged_by' => $user->id,
            'talent_pool_reason' => 'Potensial.',
        ]);
        $unflagged = Candidate::factory()->create([
            'nama_lengkap' => 'Siti Biasa',
            'email' => 'siti@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('kandidat-potensial.index', ['search' => 'example.com']));

        $response->assertOk();
        $response->assertSee('Budi Flagged');
        $response->assertDontSee('Siti Biasa');
    }

    public function test_non_hr_cannot_view_talent_pool(): void
    {
        $user = User::factory()->withRole(Role::UnitHead)->create();

        $response = $this->actingAs($user)->get(route('kandidat-potensial.index'));

        $response->assertForbidden();
    }

    public function test_hr_can_view_candidate_detail(): void
    {
        $user = User::factory()->withRole(Role::HrAdmin)->create();
        $candidate = Candidate::factory()->create([
            'nama_lengkap' => 'Budi Detail',
            'talent_pool_flagged_at' => now(),
            'talent_pool_flagged_by' => $user->id,
            'talent_pool_reason' => 'Sangat potensial.',
        ]);
        Application::factory()->create([
            'candidate_id' => $candidate->id,
            'vacancy_id' => Vacancy::factory(),
        ]);

        $response = $this->actingAs($user)->get(route('kandidat-potensial.show', $candidate));

        $response->assertOk();
        $response->assertViewIs('talent-pool.show');
        $response->assertSee('Budi Detail');
        $response->assertSee('Sangat potensial.');
    }

    public function test_candidate_detail_returns_404_without_application(): void
    {
        $user = User::factory()->withRole(Role::HrAdmin)->create();
        $candidate = Candidate::factory()->create([
            'talent_pool_flagged_at' => now(),
            'talent_pool_flagged_by' => $user->id,
            'talent_pool_reason' => 'Tanpa lamaran.',
        ]);

        $response = $this->actingAs($user)->get(route('kandidat-potensial.show', $candidate));

        $response->assertNotFound();
    }

    public function test_non_hr_cannot_view_candidate_detail(): void
    {
        $user = User::factory()->withRole(Role::UnitHead)->create();
        $candidate = Candidate::factory()->create([
            'talent_pool_flagged_at' => now(),
            'talent_pool_flagged_by' => $user->id,
            'talent_pool_reason' => 'X.',
        ]);
        Application::factory()->create([
            'candidate_id' => $candidate->id,
            'vacancy_id' => Vacancy::factory(),
        ]);

        $response = $this->actingAs($user)->get(route('kandidat-potensial.show', $candidate));

        $response->assertForbidden();
    }
}
