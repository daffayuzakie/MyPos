<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        $businessId = auth()->user()->business_id;
        $search = request('search');
        $filter = request('filter', 'all');

        $query = Product::where('business_id', $businessId)->with(['category', 'unit']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($filter === 'low') {
            $query->where('stock', '<=', 5);
        } elseif ($filter === 'empty') {
            $query->where('stock', '<=', 0);
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::where('business_id', $businessId)->get();
        $units = Unit::where('business_id', $businessId)->get();

        return view('products.index', compact('products', 'categories', 'units', 'search', 'filter'));
    }

    public function create()
    {
        $businessId = auth()->user()->business_id;
        $categories = Category::where('business_id', $businessId)->where('is_active', true)->get();
        $units = Unit::where('business_id', $businessId)->where('is_active', true)->get();

        return view('products.create', compact('categories', 'units'));
    }

    public function store(StoreProductRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $businessId = auth()->user()->business_id;
            $userId = auth()->id();

            $product = Product::create([
                'business_id' => $businessId,
                'category_id' => $request->category_id,
                'unit_id' => $request->unit_id,
                'sku' => $request->sku,
                'barcode' => $request->barcode,
                'name' => $request->name,
                'description' => $request->description,
                'purchase_price' => $request->purchase_price,
                'selling_price' => $request->selling_price,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $initialStock = (int) $request->input('stock', 0);
            if ($initialStock > 0) {
                $stockService = app(StockService::class);
                $stockService->performStockIn(
                    $product->id,
                    $initialStock,
                    $businessId,
                    $userId,
                    'Initial Stock',
                    'Stok awal produk'
                );
            }

            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil ditambahkan.');
        });
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $businessId = auth()->user()->business_id;
        $categories = Category::where('business_id', $businessId)->where('is_active', true)->get();
        $units = Unit::where('business_id', $businessId)->where('is_active', true)->get();

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        return DB::transaction(function () use ($request, $product) {
            $product->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'unit_id' => $request->unit_id,
                'sku' => $request->sku,
                'barcode' => $request->barcode,
                'description' => $request->description,
                'purchase_price' => $request->purchase_price,
                'selling_price' => $request->selling_price,
                'is_active' => $request->boolean('is_active'),
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Product updated successfully.');
        });
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        if ($product->stockMovements()->exists()) {
            Log::info('Product delete attempt blocked', [
                'product_id' => $product->id,
                'has_movements' => $product->stockMovements()->exists(),
                'expects_json' => request()->expectsJson(),
                'accept' => request()->header('Accept'),
                'xhr' => request()->header('X-Requested-With'),
                'content_type' => request()->header('Content-Type'),
                'headers' => request()->headers->all(),
            ]);

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Cannot delete product with stock movement history.'], 403);
            }

            return back()->with('error', 'Cannot delete product with stock movement history.')->withInput();
        }

        return DB::transaction(function () use ($product) {
            $product->delete();

            return redirect()->route('products.index')
                ->with('success', 'Product deleted successfully.');
        });
    }
}
