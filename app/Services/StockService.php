<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\NegativeStockException;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function __construct(
        protected Product $productModel,
        protected StockMovement $movementModel
    ) {}

    /**
     * Perform stock IN mutation
     * Formula: stock_after = stock_before + quantity (quantity > 0)
     */
    public function stockIn(int $productId, int $quantity, string $reason, ?string $note = null): StockMovement
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('IN quantity must be positive');
        }

        return DB::transaction(function () use ($productId, $quantity, $reason, $note) {
            return $this->performStockIn(
                $productId,
                $quantity,
                auth()->user()->business_id,
                auth()->id(),
                $reason,
                $note,
                null,
                null
            );
        });
    }

    /**
     * Core stock IN mutation without transaction wrapper.
     * Intended for use within an existing transaction (e.g., purchase completion).
     */
    public function performStockIn(
        int $productId,
        int $quantity,
        int $businessId,
        int $userId,
        string $reason,
        ?string $note = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('IN quantity must be positive');
        }

        $product = $this->productModel
            ->where('business_id', $businessId)
            ->where('id', $productId)
            ->lockForUpdate()
            ->firstOrFail();

        $stockBefore = $product->stock;
        $stockAfter = $stockBefore + $quantity;

        $product->stock = $stockAfter;
        $product->save();

        return $this->movementModel->create([
            'business_id' => $businessId,
            'product_id' => $productId,
            'user_id' => $userId,
            'type' => 'in',
            'quantity' => $quantity,
            'reason' => $reason,
            'note' => $note,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
        ]);
    }

    /**
     * Core stock OUT mutation without transaction wrapper.
     * Intended for use within an existing transaction (e.g., sale checkout).
     *
     * @throws InsufficientStockException
     */
    public function performStockOut(
        int $productId,
        int $quantity,
        int $businessId,
        int $userId,
        string $reason,
        ?string $note = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('OUT quantity must be positive');
        }

        $product = $this->productModel
            ->where('business_id', $businessId)
            ->where('id', $productId)
            ->lockForUpdate()
            ->firstOrFail();

        if ($product->stock < $quantity) {
            throw new InsufficientStockException(
                "Insufficient stock. Available: {$product->stock}"
            );
        }

        $stockBefore = $product->stock;
        $stockAfter = $stockBefore - $quantity;

        $product->stock = $stockAfter;
        $product->save();

        return $this->movementModel->create([
            'business_id' => $businessId,
            'product_id' => $productId,
            'user_id' => $userId,
            'type' => 'out',
            'quantity' => $quantity,
            'reason' => $reason,
            'note' => $note,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
        ]);
    }

    /**
     * Perform stock OUT mutation
     * Formula: stock_after = stock_before - quantity (qty > 0, stock_before >= qty)
     */
    public function stockOut(int $productId, int $quantity, string $reason, ?string $note = null): StockMovement
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('OUT quantity must be positive');
        }

        return DB::transaction(function () use ($productId, $quantity, $reason, $note) {
            $product = Product::where('business_id', auth()->user()->business_id)
                ->lockForUpdate()
                ->findOrFail($productId);

            if ($product->stock < $quantity) {
                throw new InsufficientStockException(
                    "Insufficient stock. Available: {$product->stock}"
                );
            }

            $stockBefore = $product->stock;
            $stockAfter = $stockBefore - $quantity;

            $product->stock = $stockAfter;
            $product->save();

            return StockMovement::create([
                'business_id' => auth()->user()->business_id,
                'product_id' => $productId,
                'user_id' => auth()->id(),
                'type' => 'out',
                'quantity' => $quantity,
                'reason' => $reason,
                'note' => $note,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ]);
        });
    }

    /**
     * Perform stock ADJUSTMENT (can be positive or negative)
     * Formula: stock_after = stock_before + quantity (qty != 0, after >= 0)
     */
    public function adjust(int $productId, int $quantity, string $reason, ?string $note = null): StockMovement
    {
        if ($quantity === 0) {
            throw new \InvalidArgumentException('Adjustment quantity cannot be zero');
        }

        return DB::transaction(function () use ($productId, $quantity, $reason, $note) {
            $product = Product::where('business_id', auth()->user()->business_id)
                ->lockForUpdate()
                ->findOrFail($productId);

            $stockBefore = $product->stock;
            $stockAfter = $stockBefore + $quantity; // quantity can be negative

            if ($stockAfter < 0) {
                throw new NegativeStockException(
                    "Adjustment would result in negative stock. Current: {$stockBefore}, Change: {$quantity}"
                );
            }

            $product->stock = $stockAfter;
            $product->save();

            return StockMovement::create([
                'business_id' => auth()->user()->business_id,
                'product_id' => $productId,
                'user_id' => auth()->id(),
                'type' => 'adjustment',
                'quantity' => $quantity, // can be negative
                'reason' => $reason,
                'note' => $note,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ]);
        });
    }
}
