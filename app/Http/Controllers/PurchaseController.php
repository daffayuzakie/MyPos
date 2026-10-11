<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    public function index(Request $request): View
    {
        $businessId = auth()->user()->business_id;
        $search = $request->get('search');

        // 3 Summary Cards
        $totalDebt = Purchase::where('business_id', $businessId)
            ->where('status', 'draft')
            ->sum('total_amount');

        $activePOCount = Purchase::where('business_id', $businessId)
            ->where('status', 'draft')
            ->count();

        $dueThisWeekCount = Purchase::where('business_id', $businessId)
            ->where('status', 'draft')
            ->whereBetween('purchase_date', [now(), now()->addDays(7)])
            ->count();

        // Suppliers List
        $suppliers = Supplier::where('business_id', $businessId)
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->withCount(['purchases as active_purchase_count' => fn ($q) => $q->where('status', 'draft')])
            ->withSum(['purchases as total_debt' => fn ($q) => $q->where('status', 'draft')], 'total_amount')
            ->orderBy('name')
            ->get();

        // Purchase Orders List
        $purchases = Purchase::where('business_id', $businessId)
            ->with(['supplier', 'user', 'items.product'])
            ->latest()
            ->paginate(15);

        // Products for PO creation dropdown
        $products = Product::where('business_id', $businessId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('purchases.index', compact(
            'suppliers',
            'purchases',
            'products',
            'totalDebt',
            'activePOCount',
            'dueThisWeekCount',
            'search'
        ));
    }

    public function show(Purchase $purchase): RedirectResponse
    {
        if ($purchase->business_id !== auth()->user()->business_id) {
            abort(403);
        }

        return redirect()->route('purchases.index');
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        if ($purchase->business_id !== auth()->user()->business_id) {
            abort(403);
        }

        if ($purchase->status !== 'draft') {
            return redirect()->route('purchases.index')
                ->with('error', 'Only draft purchases can be edited.');
        }

        $purchase->update($request->validated());

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase updated successfully.');
    }

    public function storeSupplier(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Supplier::create([
            'business_id' => auth()->user()->business_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
            'is_active' => true,
        ]);

        return redirect()->route('purchases.index')->with('status', 'Supplier baru berhasil ditambahkan.');
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $businessId = auth()->user()->business_id;
        $userId = auth()->id();

        $purchase = DB::transaction(function () use ($data, $businessId, $userId) {
            return Purchase::create([
                'business_id' => $businessId,
                'supplier_id' => $data['supplier_id'],
                'user_id' => $userId,
                'invoice_number' => $data['invoice_number'],
                'purchase_date' => $data['purchase_date'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'total_amount' => 0,
            ]);
        });

        return redirect()->route('purchases.index')->with('status', 'Purchase Order baru berhasil dibuat.');
    }

    public function complete(Purchase $purchase): RedirectResponse
    {
        if ($purchase->business_id !== auth()->user()->business_id) {
            abort(403);
        }

        if ($purchase->status !== 'draft') {
            return redirect()->route('purchases.index')
                ->with('error', 'Only draft purchases can be completed.');
        }

        try {
            $this->purchaseService->complete($purchase->id, auth()->user()->business_id, auth()->id());
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'completion' => $e->getMessage(),
            ]);
        }

        return redirect()->route('purchases.index')->with('success', 'Purchase completed successfully.');
    }
}
