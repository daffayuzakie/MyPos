<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;

    protected User $admin;

    protected User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $permissionNames = [
            'products view', 'products create', 'products edit', 'products delete',
            'categories view', 'categories create', 'categories edit', 'categories delete',
            'sales view-all', 'sales view-own', 'sales create',
        ];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $kasirRole = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        $kasirRole->syncPermissions(
            Permission::whereIn('name', ['products view', 'sales view-own', 'sales create'])
                ->where('guard_name', 'web')
                ->get()
        );

        $this->business = Business::factory()->create();

        $this->admin = User::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
        ]);
        $this->admin->assignRole('admin');

        $this->kasir = User::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
        ]);
        $this->kasir->assignRole('kasir');
    }

    public function test_admin_can_quick_add_active_category_from_products_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/categories', [
            'name' => 'Minuman',
            'description' => 'Aneka minuman',
            'is_active' => '1',
            '_category_form' => '1',
            'redirect' => 'products',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/products');

        $this->assertDatabaseHas('categories', [
            'business_id' => $this->business->id,
            'name' => 'Minuman',
            'is_active' => true,
        ]);
    }

    public function test_quick_added_category_shows_in_product_form_dropdown(): void
    {
        $this->actingAs($this->admin);

        $this->post('/categories', [
            'name' => 'Snack',
            'is_active' => '1',
            '_category_form' => '1',
            'redirect' => 'products',
        ]);

        $this->get('/products')->assertSee('Snack');
    }

    public function test_newly_created_active_category_can_be_assigned_to_product(): void
    {
        $this->actingAs($this->admin);

        $this->post('/categories', [
            'name' => 'Kopi',
            'is_active' => '1',
            '_category_form' => '1',
            'redirect' => 'products',
        ]);

        $category = Category::where('business_id', $this->business->id)->where('name', 'Kopi')->firstOrFail();

        $response = $this->post('/products', [
            'name' => 'Espresso',
            'category_id' => $category->id,
            'selling_price' => 20000,
            'stock' => 5,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'business_id' => $this->business->id,
            'name' => 'Espresso',
            'category_id' => $category->id,
        ]);
    }

    public function test_admin_can_update_category_and_redirect_to_products(): void
    {
        $this->actingAs($this->admin);

        $category = Category::factory()->create([
            'business_id' => $this->business->id,
            'is_active' => true,
        ]);

        $response = $this->put("/categories/{$category->id}", [
            'name' => 'Minuman Dingin',
            'is_active' => '1',
            '_category_form' => '1',
            'redirect' => 'products',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/products');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Minuman Dingin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_delete_category_and_redirect_to_products(): void
    {
        $this->actingAs($this->admin);

        $category = Category::factory()->create(['business_id' => $this->business->id]);

        $response = $this->delete("/categories/{$category->id}", ['redirect' => 'products']);

        $response->assertStatus(302)
            ->assertRedirect('/products');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_sees_manage_categories_button_on_products_page(): void
    {
        $this->actingAs($this->admin);

        $this->get('/products')->assertSee('manage-categories', false);
    }

    public function test_kasir_cannot_create_category(): void
    {
        $this->actingAs($this->kasir);

        $this->post('/categories', [
            'name' => 'Terlarang',
            'is_active' => '1',
        ])->assertStatus(403);

        $this->assertDatabaseMissing('categories', ['name' => 'Terlarang']);
    }

    public function test_kasir_does_not_see_manage_categories_button(): void
    {
        $this->actingAs($this->kasir);

        $this->get('/products')->assertDontSee('manage-categories', false);
    }
}
