<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function userWithBusiness(): array
    {
        $business = Business::factory()->create();
        $user = User::factory()->create(['business_id' => $business->id]);

        return [$business, $user];
    }

    public function test_dashboard_defaults_to_seven_day_chart(): void
    {
        [$business, $user] = $this->userWithBusiness();

        Sale::factory()->create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'status' => 'completed',
            'total_amount' => 50000,
            'transaction_date' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Penjualan 7 Hari Terakhir');
    }

    public function test_dashboard_today_period_shows_hourly_labels(): void
    {
        [, $user] = $this->userWithBusiness();

        $this->actingAs($user)
            ->get(route('dashboard', ['period' => 'today']))
            ->assertOk()
            ->assertSee('Penjualan Hari Ini')
            ->assertSee('23:00');
    }

    public function test_dashboard_month_period_shows_current_month_chart(): void
    {
        [, $user] = $this->userWithBusiness();

        $this->actingAs($user)
            ->get(route('dashboard', ['period' => 'month']))
            ->assertOk()
            ->assertSee('Penjualan Bulan Ini');
    }

    public function test_dashboard_invalid_period_falls_back_to_seven_days(): void
    {
        [, $user] = $this->userWithBusiness();

        $this->actingAs($user)
            ->get(route('dashboard', ['period' => 'invalid']))
            ->assertOk()
            ->assertSee('Penjualan 7 Hari Terakhir');
    }
}
