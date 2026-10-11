<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        protected Purchase $purchaseModel,
        protected PurchaseItem $purchaseItemModel,
        protected Product $productModel,
        protected StockMovement $movementModel,
        protected Supplier $supplierModel,
        protected StockService $stockService
    ) {}

    /**
     * Complete a draft purchase.
     *
     * Atomically:
     * - Locks the purchase and its items
     * - Locks each product row
     * - Updates product stock via StockService
     * - Creates stock movement records with type 'in' and purchase reference
     * - Updates purchase status to 'completed'
     *
     * @throws \InvalidArgumentException If purchase is not in draft status
     * @throws InsufficientStockException (reused for validation)
     */
    public function complete(int $purchaseId, int $businessId, int $userId): Purchase
    {
        return DB::transaction(function () use ($purchaseId, $businessId, $userId) {
            $purchase = $this->purchaseModel
                ->where('id', $purchaseId)
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($purchase->status !== 'draft') {
                throw new \InvalidArgumentException('Only draft purchases can be completed.');
            }

            // Load items with product, lock products
            $items = $this->purchaseItemModel
                ->where('purchase_id', $purchaseId)
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw new \InvalidArgumentException('Purchase has no items.');
            }

            foreach ($items as $item) {
                $this->stockService->performStockIn(
                    $item->product_id,
                    $item->quantity,
                    $businessId,
                    $userId,
                    'Purchase completion',
                    "Purchase: {$purchase->invoice_number}",
                    'purchase',
                    $purchaseId
                );
            }

            $purchase->status = 'completed';
            $purchase->save();

            return $purchase->fresh();
        });
    }

    /**
     * Validate that a purchase can be completed.
     *
     * @return array{valid: bool, message?: string}
     */
    public function canComplete(int $purchaseId, int $businessId): array
    {
        $purchase = $this->purchaseModel
            ->where('id', $purchaseId)
            ->where('business_id', $businessId)
            ->first();

        if (! $purchase) {
            return ['valid' => false, 'message' => 'Purchase not found.'];
        }

        if ($purchase->status !== 'draft') {
            return ['valid' => false, 'message' => 'Only draft purchases can be completed.'];
        }

        $items = $this->purchaseItemModel->where('purchase_id', $purchaseId)->get();

        if ($items->isEmpty()) {
            return ['valid' => false, 'message' => 'Purchase has no items.'];
        }

        return ['valid' => true];
    }
}
