<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_must_change_password_is_redirected(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('password.change'));
    }

    public function test_user_can_access_password_change_form(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $response = $this->actingAs($user)->get('/ubah-password');

        $response->assertStatus(200);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $response = $this->actingAs($user)->post('/ubah-password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $response = $this->actingAs($user)->post('/ubah-password', [
            'current_password' => 'wrong-password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_new_password_must_be_confirmed(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $response = $this->actingAs($user)->post('/ubah-password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_user_without_flag_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }
}
