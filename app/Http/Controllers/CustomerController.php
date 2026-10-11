<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::where('business_id', auth()->user()->business_id);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['business_id'] = auth()->user()->business_id;

        $customer = Customer::create($validated);

        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->business_id !== auth()->user()->business_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function destroy(Customer $customer)
    {
        if ($customer->business_id !== auth()->user()->business_id) {
            abort(403);
        }

        $customer->delete();

        return response()->json(['success' => true]);
    }

    public function show(Customer $customer)
    {
        if ($customer->business_id !== auth()->user()->business_id) {
            abort(403);
        }

        return response()->json($customer);
    }
}
