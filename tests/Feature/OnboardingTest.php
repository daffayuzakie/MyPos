<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $business = Business::factory()->create();

        return User::factory()->create(['business_id' => $business->id]);
    }

    public function test_owner_can_view_onboarding(): void
    {
        $user = $this->owner();

        $this->actingAs($user)
            ->get(route('onboarding.show'))
            ->assertOk()
            ->assertSee('Profil Usaha');
    }

    public function test_business_step_saves_profile_and_advances(): void
    {
        $user = $this->owner();

        $this->actingAs($user)
            ->post(route('onboarding.business'), [
                'name' => 'Toko Maju',
                'type' => 'Retail',
                'timezone' => 'Asia/Jakarta',
            ])
            ->assertRedirect(route('onboarding.show'));

        $this->assertDatabaseHas('businesses', [
            'id' => $user->business_id,
            'name' => 'Toko Maju',
            'type' => 'Retail',
            'timezone' => 'Asia/Jakarta',
        ]);
        $this->assertEquals(2, session('onboarding.step'));
    }

    public function test_branch_step_creates_primary_branch_and_assigns_owner(): void
    {
        $user = $this->owner();

        $this->actingAs($user)
            ->post(route('onboarding.branch'), [
                'name' => 'Cabang Utama',
                'phone' => '08123456789',
                'address' => 'Jl. Mawar No. 1',
            ])
            ->assertRedirect(route('onboarding.show'));

        $branch = Branch::where('business_id', $user->business_id)->first();

        $this->assertNotNull($branch);
        $this->assertTrue($branch->is_primary);
        $this->assertEquals($branch->id, $user->fresh()->branch_id);
    }

    public function test_preferences_step_saves_tax_and_payment_methods(): void
    {
        $user = $this->owner();

        $this->actingAs($user)
            ->post(route('onboarding.preferences'), [
                'tax_percentage' => 10,
                'payment_methods' => ['cash', 'qris'],
            ])
            ->assertRedirect(route('onboarding.show'));

        $this->assertDatabaseHas('businesses', [
            'id' => $user->business_id,
            'tax_percentage' => 10,
        ]);
        $this->assertEquals(['cash', 'qris'], $user->business->fresh()->payment_methods);
    }

    public function test_product_step_creates_product_with_initial_stock(): void
    {
        $user = $this->owner();

        $this->actingAs($user)
            ->post(route('onboarding.products'), [
                'name' => 'Kopi Susu',
                'selling_price' => 15000,
                'stock' => 5,
                'unit_name' => 'Cup',
            ])
            ->assertRedirect(route('onboarding.show'));

        $this->assertDatabaseHas('products', [
            'business_id' => $user->business_id,
            'name' => 'Kopi Susu',
            'stock' => 5,
        ]);
    }

    public function test_complete_marks_onboarded_and_redirects_to_pos(): void
    {
        $user = $this->owner();

        $this->actingAs($user)
            ->post(route('onboarding.complete'))
            ->assertRedirect(route('pos.index'));

        $this->assertNotNull($user->business->fresh()->onboarded_at);
    }

    public function test_onboarding_redirects_to_pos_when_already_complete(): void
    {
        $user = $this->owner();
        $user->business->forceFill(['onboarded_at' => now()])->save();

        $this->actingAs($user)
            ->get(route('onboarding.show'))
            ->assertRedirect(route('pos.index'));
    }
}
