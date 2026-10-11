<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\View\View;

class SaleController extends Controller
{
    /**
     * Display sales history.
     */
    public function index(): View
    {
        $businessId = auth()->user()->business_id;
        $search = request('search');
        $status = request('status');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        $sales = Sale::where('business_id', $businessId)
            ->with(['user', 'customer', 'items.product', 'payments'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('transaction_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('transaction_date', '<=', $dateTo);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', compact('sales', 'search', 'status', 'dateFrom', 'dateTo'));
    }

    /**
     * Display a specific sale.
     */
    public function show(Sale $sale): View
    {
        if ($sale->business_id !== auth()->user()->business_id) {
            abort(403, 'Unauthorized access to sale.');
        }

        $sale->load(['user', 'items.product', 'payments']);

        return view('sales.show', compact('sale'));
    }
}
