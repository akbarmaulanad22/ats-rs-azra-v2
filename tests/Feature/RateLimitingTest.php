<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    // ── Public browse (30/min by IP) ──────────────────────────────────────

    public function test_career_index_allows_30_requests_per_minute(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $response = $this->get(route('karier.index'));
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        $this->get(route('karier.index'))->assertStatus(429);
    }

    // ── Token access (10/min by token) ────────────────────────────────────

    public function test_test_show_allows_10_requests_per_minute(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get('/tes/fake-token');
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        $this->get('/tes/fake-token')->assertStatus(429);
    }

    public function test_different_tokens_have_separate_rate_limits(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->get('/tes/token-a');
        }

        $this->get('/tes/token-a')->assertStatus(429);

        $response = $this->get('/tes/token-b');
        $this->assertNotEquals(429, $response->getStatusCode());
    }

    // ── Public submit (5/min by token or IP) ──────────────────────────────

    public function test_test_submit_allows_5_requests_per_minute(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/tes/fake-token');
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        $this->post('/tes/fake-token')->assertStatus(429);
    }

    // ── Password change (5/min, auth required) ────────────────────────────

    public function test_password_change_allows_5_requests_per_minute(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($user)->post(route('password.update'));
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        $this->actingAs($user)->post(route('password.update'))->assertStatus(429);
    }

    // ── Offering routes (10/min by offering, signed + throttled) ─────────

    public function test_offering_routes_are_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get('/penawaran/1/terima');
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        $this->get('/penawaran/1/terima')->assertStatus(429);
    }

    public function test_different_offerings_have_separate_rate_limits(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->get('/penawaran/1/terima');
        }

        $this->get('/penawaran/1/terima')->assertStatus(429);

        $response = $this->get('/penawaran/2/terima');
        $this->assertNotEquals(429, $response->getStatusCode());
    }
}
