<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\HeldTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Services\DiscountService;
use App\Services\MidtransService;
use App\Services\PaymentService;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PosController extends Controller
{
    /**
     * Maximum number of products returned to the POS grid per request.
     * Keeps the initial page payload and search responses bounded.
     */
    private const PRODUCT_PAGE_SIZE = 100;

    public function index(MidtransService $midtrans)
    {
        $businessId = auth()->user()->business_id;

        $products = Product::with('category:id,name')
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(self::PRODUCT_PAGE_SIZE)
            ->get(['id', 'name', 'sku', 'barcode', 'selling_price', 'stock', 'category_id', 'image']);
        $categories = Category::where('business_id', $businessId)->get();
        $heldTransactionsCount = HeldTransaction::where('business_id', $businessId)->count();

        $midtransClientKey = $midtrans->clientKey();
        $midtransSnapUrl = $midtrans->snapScriptUrl();
        $midtransConfigured = $midtrans->isConfigured() && ! empty($midtransClientKey);

        return view('pos.index', compact(
            'products',
            'categories',
            'heldTransactionsCount',
            'midtransClientKey',
            'midtransSnapUrl',
            'midtransConfigured',
        ));
    }

    public function searchProducts(Request $request)
    {
        $query = Product::with('category:id,name')
            ->where('business_id', auth()->user()->business_id)
            ->where('is_active', true);

        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('sku', 'like', "%{$searchTerm}%")
                    ->orWhere('barcode', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query
            ->orderBy('name')
            ->limit(self::PRODUCT_PAGE_SIZE)
            ->get(['id', 'name', 'sku', 'barcode', 'selling_price', 'stock', 'category_id', 'image']);

        return response()->json($products);
    }

    public function checkout(Request $request, SaleService $saleService, MidtransService $midtrans, PaymentService $paymentService)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'nullable|in:cash,midtrans',
            'received_cash' => 'required_if:payment_method,cash|numeric|nullable',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric',
            'voucher_code' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
            'notes' => 'nullable|string',
        ]);

        $userId = auth()->id();
        $businessId = auth()->user()->business_id;
        $paymentMethod = $request->payment_method ?? 'cash';

        try {
            if ($paymentMethod === 'midtrans') {
                return $this->checkoutMidtrans($request, $saleService, $midtrans, $paymentService, $businessId, $userId);
            }

            $result = $saleService->checkout(
                $businessId,
                $userId,
                $request->items,
                (float) ($request->received_cash ?? 0),
                $request->notes,
                $paymentMethod,
                $request->customer_id,
                $request->discount_type,
                $request->discount_value,
                $request->voucher_code
            );

            $sale = $result['sale'];
            $change = $result['change'];

            $sale->load(['items.product', 'customer', 'user', 'payments']);

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully.',
                'sale' => $sale,
                'change' => $change,
                'payment' => $result['payment'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Create a pending Midtrans sale, reserve its stock and request a Snap token.
     */
    protected function checkoutMidtrans(Request $request, SaleService $saleService, MidtransService $midtrans, PaymentService $paymentService, int $businessId, int $userId)
    {
        $result = $saleService->createPendingSale(
            $businessId,
            $userId,
            $request->items,
            $request->customer_id,
            $request->discount_type,
            $request->discount_value,
            $request->voucher_code
        );

        $sale = $result['sale'];
        $payment = $result['payment'];

        try {
            $snap = $midtrans->createSnapTransaction($sale);
        } catch (\Throwable $e) {
            // Roll back the pending sale/stock reservation if Snap cannot be created.
            $paymentService->markSaleFailed($payment, 'failed');

            return response()->json(['error' => $e->getMessage()], 400);
        }

        $payment->update([
            'metadata' => array_merge($payment->metadata ?? [], [
                'snap_token' => $snap['token'] ?? null,
                'redirect_url' => $snap['redirect_url'] ?? null,
            ]),
        ]);

        $sale->load(['items.product', 'customer', 'user', 'payments']);

        return response()->json([
            'success' => true,
            'pending' => true,
            'message' => 'Transaksi dibuat. Menunggu pembayaran non-tunai.',
            'payment_method' => 'midtrans',
            'sale' => $sale,
            'payment' => $payment->fresh(),
            'snap_token' => $snap['token'] ?? null,
            'redirect_url' => $snap['redirect_url'] ?? null,
        ]);
    }

    /**
     * Poll the status of a sale (used while waiting for a Midtrans webhook).
     */
    public function getSaleStatus(int $saleId, PaymentService $paymentService)
    {
        $sale = Sale::with(['payments', 'items.product', 'customer'])
            ->where('business_id', auth()->user()->business_id)
            ->findOrFail($saleId);

        $payment = $sale->payments->sortByDesc('id')->first();

        if ($sale->status === 'pending'
            && $payment
            && $payment->provider === 'midtrans'
            && $payment->status === 'pending') {
            $this->reconcileMidtransIfDue($paymentService, $payment);

            $sale = $sale->fresh(['payments', 'items.product', 'customer']);
            $payment = $sale->payments->sortByDesc('id')->first();
        }

        return response()->json([
            'sale_id' => $sale->id,
            'status' => $sale->status,
            'payment_status' => $payment?->status,
            'total_amount' => $sale->total_amount,
            'invoice_number' => $sale->invoice_number,
        ]);
    }

    /**
     * Pull the latest status from Midtrans at most once per throttle window.
     */
    protected function reconcileMidtransIfDue(PaymentService $paymentService, Payment $payment): void
    {
        $key = "midtrans:reconcile:{$payment->id}";
        $throttle = (int) config('midtrans.reconcile_throttle_seconds', 5);

        if (Cache::add($key, true, now()->addSeconds($throttle))) {
            $paymentService->reconcileWithMidtrans($payment);
        }
    }

    public function holdTransaction(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string',
            'subtotal' => 'required|numeric',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        HeldTransaction::create([
            'business_id' => auth()->user()->business_id,
            'user_id' => auth()->id(),
            'customer_id' => $request->customer_id,
            'customer_name' => $request->customer_name,
            'items' => $request->items,
            'subtotal' => $request->subtotal,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'notes' => $request->notes,
            'held_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function getHeldTransactions()
    {
        $held = HeldTransaction::where('business_id', auth()->user()->business_id)
            ->where('user_id', auth()->id())
            ->get();

        return response()->json($held);
    }

    public function resumeTransaction(int $id)
    {
        $held = HeldTransaction::where('business_id', auth()->user()->business_id)
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        $data = $held->toArray();
        $held->delete();

        return response()->json($data);
    }

    public function deleteHeldTransaction(int $id)
    {
        $held = HeldTransaction::where('business_id', auth()->user()->business_id)
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        $held->delete();

        return response()->json(['success' => true]);
    }

    public function getReceipt(int $saleId)
    {
        $sale = Sale::with(['items.product', 'payments', 'customer', 'business'])
            ->where('business_id', auth()->user()->business_id)
            ->findOrFail($saleId);

        return response()->json($sale);
    }

    public function applyDiscount(Request $request, DiscountService $discountService)
    {
        $request->validate([
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric',
            'subtotal' => 'required|numeric',
        ]);

        $discountAmount = $discountService->calculateDiscount($request->subtotal, $request->type, $request->value);

        return response()->json(['discount_amount' => $discountAmount]);
    }

    public function searchCustomers(Request $request)
    {
        $query = Customer::where('business_id', auth()->user()->business_id);

        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }

        return response()->json(
            $query->orderBy('name')->limit(20)->get(['id', 'name', 'phone', 'email', 'level'])
        );
    }
}
