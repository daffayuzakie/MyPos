<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function userWithBranches(int $branchCount = 1): User
    {
        $business = Business::factory()->create();
        $user = User::factory()->create(['business_id' => $business->id]);

        Branch::factory()->count($branchCount)->create(['business_id' => $business->id]);

        return $user;
    }

    public function test_branch_selection_screen_lists_multiple_branches(): void
    {
        $user = $this->userWithBranches(2);

        $this->actingAs($user)
            ->get(route('branches.select'))
            ->assertOk()
            ->assertSee('Pilih Cabang');
    }

    public function test_branch_selection_redirects_when_only_one_branch(): void
    {
        $user = $this->userWithBranches(1);

        $this->actingAs($user)
            ->get(route('branches.select'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_switching_branch_stores_active_branch_in_session(): void
    {
        $user = $this->userWithBranches(2);
        $target = Branch::where('business_id', $user->business_id)->first();

        $this->actingAs($user)
            ->post(route('branches.switch'), ['branch_id' => $target->id])
            ->assertRedirect(route('dashboard'));

        $this->assertEquals($target->id, session('active_branch_id'));
    }

    public function test_switching_to_foreign_branch_is_rejected(): void
    {
        $user = $this->userWithBranches(1);
        $foreign = Branch::factory()->create();

        $this->actingAs($user)
            ->post(route('branches.switch'), ['branch_id' => $foreign->id])
            ->assertSessionHasErrors('branch_id');
    }

    public function test_kasir_login_redirects_to_pos(): void
    {
        Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

        $business = Business::factory()->create();
        $branch = Branch::factory()->create(['business_id' => $business->id]);
        $kasir = User::factory()->create([
            'business_id' => $business->id,
            'branch_id' => $branch->id,
        ]);
        $kasir->assignRole('kasir');

        $response = $this->post('/login', [
            'email' => $kasir->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('pos.index'));
        $this->assertAuthenticatedAs($kasir);
    }

    public function test_owner_with_multiple_branches_redirects_to_branch_selection_on_login(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $user = $this->userWithBranches(2);
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('branches.select'));
        $this->assertAuthenticatedAs($user);
    }
}
