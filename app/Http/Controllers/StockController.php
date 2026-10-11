<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\NegativeStockException;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function index(): View
    {
        $businessId = auth()->user()->business_id;
        $search = request('search');
        $type = request('type');

        $movements = StockMovement::where('business_id', auth()->user()->business_id)
            ->with(['product', 'user'])
            ->when(request('search'), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reason', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%");
                        });
                });
            })
            ->when(request('type'), function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('stock.index', compact('movements'));
    }

    public function store(StoreStockMovementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            if ($data['type'] === 'in') {
                $this->stockService->stockIn(
                    $data['product_id'],
                    $data['quantity'],
                    $data['reason'],
                    $data['note'] ?? null
                );
            } elseif ($data['type'] === 'out') {
                $this->stockService->stockOut(
                    $data['product_id'],
                    $data['quantity'],
                    $data['reason'],
                    $data['note'] ?? null
                );
            } else {
                $this->stockService->adjust(
                    $data['product_id'],
                    $data['quantity'],
                    $data['reason'],
                    $data['note'] ?? null
                );
            }
        } catch (InsufficientStockException $e) {
            throw ValidationException::withMessages([
                'quantity' => $e->getMessage(),
            ]);
        } catch (NegativeStockException $e) {
            throw ValidationException::withMessages([
                'quantity' => $e->getMessage(),
            ]);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'quantity' => $e->getMessage(),
            ]);
        }

        return redirect()->route('stock.index')->with('success', 'Stock movement created successfully.');
    }
}
