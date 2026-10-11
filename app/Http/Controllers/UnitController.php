<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $businessId = auth()->user()->business_id;
        $search = $request->get('search');

        $units = Unit::where('business_id', $businessId)
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            }))
            ->paginate(10)
            ->withQueryString();

        return view('units.index', compact('units', 'search'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(StoreUnitRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $unit = Unit::create([
                'business_id' => auth()->user()->business_id,
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'is_active' => $request->boolean('is_active'),
            ]);

            return redirect()->route('units.index')
                ->with('success', 'Unit created successfully.');
        });
    }

    public function edit(Unit $unit)
    {
        $this->authorize('update', $unit);

        return view('units.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $this->authorize('update', $unit);

        return DB::transaction(function () use ($request, $unit) {
            $unit->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'is_active' => $request->boolean('is_active'),
            ]);

            return redirect()->route('units.index')
                ->with('success', 'Unit updated successfully.');
        });
    }

    public function destroy(Unit $unit)
    {
        $this->authorize('delete', $unit);

        return DB::transaction(function () use ($unit) {
            $unit->delete();

            return redirect()->route('units.index')
                ->with('success', 'Unit deleted successfully.');
        });
    }
}
