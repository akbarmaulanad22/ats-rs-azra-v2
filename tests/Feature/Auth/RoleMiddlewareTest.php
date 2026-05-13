<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:hr_admin'])->get('/test-admin-only', fn () => 'ok');
        Route::middleware(['web', 'auth', 'role:hr_admin,hr_manager'])->get('/test-hr-only', fn () => 'ok');
    }

    public function test_hr_admin_can_access_admin_routes(): void
    {
        $user = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($user)->get('/test-admin-only');

        $response->assertStatus(200);
    }

    public function test_employee_cannot_access_admin_routes(): void
    {
        $user = User::factory()->withRole(Role::Employee)->create();

        $response = $this->actingAs($user)->get('/test-admin-only');

        $response->assertStatus(403);
    }

    public function test_hr_manager_can_access_hr_routes(): void
    {
        $user = User::factory()->withRole(Role::HrManager)->create();

        $response = $this->actingAs($user)->get('/test-hr-only');

        $response->assertStatus(200);
    }

    public function test_unit_head_cannot_access_hr_only_routes(): void
    {
        $user = User::factory()->withRole(Role::UnitHead)->create();

        $response = $this->actingAs($user)->get('/test-hr-only');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
