<?php

namespace App\Services;

use App\Exceptions\InsufficientStockReservationException;
use App\Exceptions\InvalidStockReservationStateException;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockReservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StockReservationService
{
    /**
     * Create a new stock reservation.
     *
     * @throws InsufficientStockReservationException
     */
    public function create(int $businessId, int $saleId, int $productId, int $quantity, ?Carbon $expiresAt = null): StockReservation
    {
        if ($businessId <= 0) {
            throw new \InvalidArgumentException('businessId must be greater than 0');
        }
        if ($saleId <= 0) {
            throw new \InvalidArgumentException('saleId must be greater than 0');
        }
        if ($productId <= 0) {
            throw new \InvalidArgumentException('productId must be greater than 0');
        }
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('quantity must be greater than 0');
        }

        return DB::transaction(function () use ($businessId, $saleId, $productId, $quantity, $expiresAt) {
            // 1. Retrieve Product using business_id + product_id with lockForUpdate()
            $product = Product::where('business_id', $businessId)
                ->where('id', $productId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Validate sale belongs to the same business
            $sale = Sale::where('id', $saleId)
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->firstOrFail();

            // 3. Calculate active reserved quantity for this product (same business_id scope)
            $reservedQuantity = StockReservation::where('business_id', $businessId)
                ->where('product_id', $productId)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->sum('quantity');

            // 4. Calculate available stock
            $available = $product->stock - $reservedQuantity;

            // 5. Validate available stock
            if ($quantity > $available) {
                throw new InsufficientStockReservationException(
                    "Insufficient stock for reservation. Available: {$available}, Requested: {$quantity}"
                );
            }

            // 6. Create reservation
            $reservation = StockReservation::create([
                'business_id' => $businessId,
                'sale_id' => $saleId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'status' => 'active',
                'expires_at' => $expiresAt ?? Carbon::now()->addMinutes(15),
            ]);

            return $reservation;
        });
    }

    /**
     * Release a stock reservation.
     *
     * @throws InvalidStockReservationStateException
     */
    public function release(int $reservationId, int $businessId): StockReservation
    {
        return DB::transaction(function () use ($reservationId, $businessId) {
            // 1. Find reservation by id + business_id with lockForUpdate()
            $reservation = StockReservation::where('id', $reservationId)
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Handle state transitions
            if ($reservation->isReleased()) {
                // Already released - idempotent, return unchanged
                return $reservation;
            }

            if ($reservation->isConverted()) {
                throw new InvalidStockReservationStateException(
                    'Cannot release reservation that has already been converted.'
                );
            }

            if ($reservation->isExpired()) {
                // Expired - treat as already unavailable, do not resurrect
                throw new InvalidStockReservationStateException(
                    'Cannot release expired reservation.'
                );
            }

            // 3. If active: status → released
            if ($reservation->isActive()) {
                $reservation->status = 'released';
                $reservation->save();
            }

            return $reservation;
        });
    }

    /**
     * Convert a stock reservation (active → converted).
     *
     * This means the reservation has been consumed by the sale/payment flow.
     * Does NOT decrement Product.stock in this phase - that will be
     * orchestrated with StockService in the payment flow later.
     *
     * @throws InvalidStockReservationStateException
     */
    public function convert(int $reservationId, int $businessId): StockReservation
    {
        return DB::transaction(function () use ($reservationId, $businessId) {
            // 1. Find reservation by id + business_id with lockForUpdate()
            $reservation = StockReservation::where('id', $reservationId)
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Handle state transitions
            if ($reservation->isConverted()) {
                // Already converted - idempotent success
                return $reservation;
            }

            if ($reservation->isReleased()) {
                throw new InvalidStockReservationStateException(
                    'Cannot convert a released reservation.'
                );
            }

            if ($reservation->isExpired()) {
                throw new InvalidStockReservationStateException(
                    'Cannot convert an expired reservation.'
                );
            }

            // 3. If active: status → converted
            if ($reservation->isActive()) {
                $reservation->status = 'converted';
                $reservation->save();
            }

            return $reservation;
        });
    }

    /**
     * Expire a stock reservation.
     *
     * Expired reservations are treated as automatically unavailable.
     * Does NOT modify Product.stock.
     *
     * @throws InvalidStockReservationStateException
     */
    public function expire(int $reservationId, int $businessId): StockReservation
    {
        return DB::transaction(function () use ($reservationId, $businessId) {
            // 1. Find reservation by id + business_id with lockForUpdate()
            $reservation = StockReservation::where('id', $reservationId)
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Handle state transitions
            // Only idempotent if status is already explicitly 'expired'
            if ($reservation->status === 'expired') {
                return $reservation;
            }

            if ($reservation->isConverted()) {
                throw new InvalidStockReservationStateException(
                    'Cannot expire a converted reservation.'
                );
            }

            if ($reservation->isReleased()) {
                throw new InvalidStockReservationStateException(
                    'Cannot expire a released reservation.'
                );
            }

            // 3. If active (including timestamp-expired): status → expired
            if ($reservation->isActive() || ($reservation->status === 'active' && $reservation->expires_at && $reservation->expires_at->isPast())) {
                $reservation->status = 'expired';
                $reservation->save();
            }

            return $reservation;
        });
    }

    /**
     * Calculate available stock for a product.
     *
     * Available = product.stock - SUM(active unexpired reservation quantities)
     *
     * @return int Never returns negative. Returns 0 if reserved > stock (data inconsistency).
     */
    public function availableStock(int $businessId, int $productId): int
    {
        $reservedQuantity = StockReservation::where('business_id', $businessId)
            ->where('product_id', $productId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->sum('quantity');

        $product = Product::where('business_id', $businessId)
            ->where('id', $productId)
            ->firstOrFail();

        $available = $product->stock - $reservedQuantity;

        // Never return negative - prefer 0 for data inconsistency
        return $available > 0 ? $available : 0;
    }

    /**
     * Release all active reservations for a sale.
     *
     * This is intended to be called when a pending sale is cancelled.
     * Does NOT modify Product.stock or create StockMovement records.
     *
     * @return int Number of reservations released
     */
    public function releaseBySale(int $saleId, int $businessId): int
    {
        return DB::transaction(function () use ($saleId, $businessId) {
            $reservations = StockReservation::where('business_id', $businessId)
                ->where('sale_id', $saleId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get();

            $releasedCount = 0;
            foreach ($reservations as $reservation) {
                $reservation->status = 'released';
                $reservation->save();
                $releasedCount++;
            }

            return $releasedCount;
        });
    }
}
