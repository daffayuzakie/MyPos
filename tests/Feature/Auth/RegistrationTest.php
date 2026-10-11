<?php

namespace Tests\Feature\Auth;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register-business');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_business(): void
    {
        // Ensure admin role exists
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

        $response = $this->post('/register-business', [
            'business_name' => 'Test Business',
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('onboarding.show', absolute: false));

        // Verify business was created
        $business = Business::where('name', 'Test Business')->first();
        $this->assertNotNull($business);
        $this->assertEquals('Test Business', $business->name);
        $this->assertNotNull($business->slug);

        // Verify user was created and linked to business
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Test Admin', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals($business->id, $user->business_id);
        $this->assertEquals('active', $user->status);

        // Verify user has admin role
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_registration_rolls_back_on_failure(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

        // Test with duplicate email - should fail and rollback
        Business::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register-business', [
            'business_name' => 'Test Business',
            'name' => 'Test Admin',
            'email' => 'existing@example.com', // Duplicate email
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');

        // Business should not be created (rollback)
        $this->assertEquals(1, Business::count());
    }

    public function test_registration_requires_matching_passwords(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $response = $this->post('/register-business', [
            'business_name' => 'Test Business',
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_registration_requires_business_name(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $response = $this->post('/register-business', [
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('business_name');
    }
}
