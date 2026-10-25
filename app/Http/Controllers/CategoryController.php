<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $businessId = auth()->user()->business_id;
        $search = $request->get('search');

        $categories = Category::where('business_id', $businessId)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories', 'search'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $category = Category::create([
                'business_id' => auth()->user()->business_id,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
            ]);

            return $this->redirectAfter($request)
                ->with('success', 'Category created successfully.');
        });
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);

        return DB::transaction(function () use ($request, $category) {
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
            ]);

            return $this->redirectAfter($request)
                ->with('success', 'Category updated successfully.');
        });
    }

    public function destroy(Request $request, Category $category)
    {
        $this->authorize('delete', $category);

        return DB::transaction(function () use ($request, $category) {
            $category->delete();

            return $this->redirectAfter($request)
                ->with('success', 'Category deleted successfully.');
        });
    }

    /**
     * Redirect back to the products page when the action originated there,
     * otherwise fall back to the categories index.
     */
    private function redirectAfter(Request $request)
    {
        if ($request->input('redirect') === 'products') {
            return redirect()->route('products.index');
        }

        return redirect()->route('categories.index');
    }
}
