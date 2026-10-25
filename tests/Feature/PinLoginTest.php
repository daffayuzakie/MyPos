<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PinLoginTest extends TestCase
{
    use RefreshDatabase;

    private function business(string $slug = 'toko-test'): Business
    {
        return Business::factory()->create(['slug' => $slug]);
    }

    private function kasir(Business $business, string $pin, bool $assignRole = true): User
    {
        $branch = Branch::factory()->create(['business_id' => $business->id]);

        $user = User::factory()->create([
            'business_id' => $business->id,
            'branch_id' => $branch->id,
            'pin' => $pin,
        ]);

        if ($assignRole) {
            Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
            $user->assignRole('kasir');
        }

        return $user;
    }

    public function test_guest_can_view_pin_login_page(): void
    {
        $this->get(route('pin.login'))
            ->assertOk()
            ->assertSee('Kode Usaha');
    }

    public function test_kasir_can_login_with_business_code_and_pin(): void
    {
        $business = $this->business();
        $kasir = $this->kasir($business, '1234');

        $response = $this->post(route('pin.login.submit'), [
            'business_code' => 'toko-test',
            'pin' => '1234',
        ]);

        $response->assertRedirect(route('pos.index'));
        $this->assertAuthenticatedAs($kasir);
    }

    public function test_non_kasir_pin_login_redirects_to_dashboard(): void
    {
        $business = $this->business();
        $user = $this->kasir($business, '5678', assignRole: false);

        $this->post(route('pin.login.submit'), [
            'business_code' => 'toko-test',
            'pin' => '5678',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_pin_is_rejected(): void
    {
        $business = $this->business();
        $this->kasir($business, '1234');

        $this->post(route('pin.login.submit'), [
            'business_code' => 'toko-test',
            'pin' => '0000',
        ])->assertSessionHasErrors('pin');

        $this->assertGuest();
    }

    public function test_unknown_business_code_is_rejected(): void
    {
        $this->post(route('pin.login.submit'), [
            'business_code' => 'tidak-ada',
            'pin' => '1234',
        ])->assertSessionHasErrors('business_code');

        $this->assertGuest();
    }

    public function test_pin_from_another_business_is_rejected(): void
    {
        $first = $this->business('toko-satu');
        $second = $this->business('toko-dua');

        $this->kasir($first, '1234');
        $this->kasir($second, '4321');

        $this->post(route('pin.login.submit'), [
            'business_code' => 'toko-satu',
            'pin' => '4321',
        ])->assertSessionHasErrors('pin');

        $this->assertGuest();
    }
}
