<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\Sale;
use App\Services\RefundService;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function index()
    {
        $refunds = Refund::with(['sale', 'refundItems.product'])
            ->where('business_id', auth()->user()->business_id)
            ->latest()
            ->paginate(15);

        return view('sales.index', compact('refunds'));
    }

    public function searchSale(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string',
        ]);

        $sale = Sale::with(['items.product', 'customer'])
            ->where('business_id', auth()->user()->business_id)
            ->where('invoice_number', $request->invoice_number)
            ->first();

        if (! $sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        return response()->json($sale);
    }

    public function process(Request $request, RefundService $refundService)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'items' => 'required|array',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.reason' => 'nullable|string',
            'reason' => 'required|string',
        ]);

        try {
            $refund = $refundService->processRefund(
                auth()->user()->business_id,
                auth()->id(),
                $request->sale_id,
                $request->items,
                $request->reason
            );

            return response()->json(['success' => true, 'refund' => $refund]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
