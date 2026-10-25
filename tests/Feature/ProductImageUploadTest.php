<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $admin;
    protected Category $category;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $permissionNames = [
            'products view', 'products create', 'products edit', 'products delete',
        ];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->business = Business::factory()->create();

        $this->admin = User::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'active',
        ]);
        $this->admin->assignRole('admin');

        $this->category = Category::create([
            'business_id' => $this->business->id,
            'name' => 'Food',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pcs',
            'code' => 'pcs',
            'is_active' => true,
        ]);
    }

    public function test_can_create_product_with_image(): void
    {
        $file = UploadedFile::fake()->image('burger.jpg', 400, 400);

        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => 'Burger Jumbo',
            'sku' => 'BG-001',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'selling_price' => 35000,
            'purchase_price' => 20000,
            'stock' => 10,
            'is_active' => 1,
            'image' => $file,
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'BG-001')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_can_update_product_and_replace_image(): void
    {
        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('products', 'public');

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Es Teh Manis',
            'sku' => 'ETM-001',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'selling_price' => 5000,
            'purchase_price' => 2000,
            'stock' => 50,
            'is_active' => true,
            'image' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAs($this->admin)->put(route('products.update', $product), [
            'name' => 'Es Teh Manis Jumbo',
            'sku' => 'ETM-001',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'selling_price' => 7000,
            'purchase_price' => 2500,
            'stock' => 50,
            'is_active' => 1,
            'image' => $newFile,
        ]);

        $response->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertNotEquals($oldPath, $product->image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($product->image);
    }
}
