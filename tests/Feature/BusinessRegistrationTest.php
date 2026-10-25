<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_logs_in_and_redirects_to_dashboard_without_onboarding(): void
    {
        $response = $this->post(route('business.register.submit'), [
            'business_name' => 'Toko Baru',
            'name' => 'Pemilik Toko',
            'email' => 'pemilik@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'business_code' => 'toko-baru',
            'type' => 'retail_store',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        // Dashboard harus bisa dibuka langsung setelah registrasi (tanpa onboarding).
        $this->get(route('dashboard'))->assertOk();

        $this->assertDatabaseHas('businesses', [
            'name' => 'Toko Baru',
            'slug' => 'toko-baru',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Pemilik Toko',
            'email' => 'pemilik@example.com',
        ]);

        $user = User::where('email', 'pemilik@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('admin'));
        $this->assertSame(
            Business::where('slug', 'toko-baru')->value('id'),
            $user->business_id
        );
    }

    public function test_onboarding_route_is_no_longer_available(): void
    {
        $this->get('/onboarding')->assertNotFound();
    }
}
