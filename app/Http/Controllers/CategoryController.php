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

            return redirect()->route('categories.index')
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

            return redirect()->route('categories.index')
                ->with('success', 'Category updated successfully.');
        });
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        return DB::transaction(function () use ($category) {
            $category->delete();

            return redirect()->route('categories.index')
                ->with('success', 'Category deleted successfully.');
        });
    }
}
