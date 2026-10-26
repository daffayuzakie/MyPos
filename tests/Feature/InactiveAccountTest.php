<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveAccountTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::factory()->create();
    }

    public function test_inactive_user_cannot_login_with_password(): void
    {
        User::factory()->create([
            'business_id' => $this->business->id,
            'email' => 'nonaktif@example.com',
            'password' => 'password',
            'status' => User::STATUS_INACTIVE,
        ]);

        $response = $this->post('/login', [
            'email' => 'nonaktif@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_user_cannot_login_with_pin(): void
    {
        User::factory()->create([
            'business_id' => $this->business->id,
            'pin' => '1234',
            'status' => User::STATUS_INACTIVE,
        ]);

        $response = $this->post('/pin-login', [
            'business_code' => $this->business->slug,
            'pin' => '1234',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('pin');
    }

    public function test_inactive_session_is_terminated_on_next_request(): void
    {
        $user = User::factory()->create([
            'business_id' => $this->business->id,
            'status' => User::STATUS_INACTIVE,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_active_user_can_still_login_with_password(): void
    {
        $user = User::factory()->create([
            'business_id' => $this->business->id,
            'email' => 'aktif@example.com',
            'password' => 'password',
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->post('/login', [
            'email' => 'aktif@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }
}
