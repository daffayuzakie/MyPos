<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_landing_page_shows_sign_in_and_sign_up_actions(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Daftar Gratis');
        $response->assertSee('Masuk');
    }

    public function test_landing_page_keeps_sign_in_and_sign_up_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('Daftar Gratis');
        $response->assertSee('Masuk');
    }
}
