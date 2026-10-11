<?php

namespace App\Services;

use App\Exceptions\InvalidStockReservationStateException;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected StockService $stockService,
        protected StockReservationService $reservationService
    ) {}

    /**
     * Mark a pending (Midtrans) sale as paid and settle its stock.
     *
     * Idempotent: repeated webhooks for an already-paid payment are ignored.
     */
    public function markSalePaid(Payment $payment, array $payload = []): Sale
    {
        return DB::transaction(function () use ($payment, $payload) {
            $sale = Sale::where('id', $payment->sale_id)->lockForUpdate()->firstOrFail();

            $payment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status === 'paid') {
                return $sale;
            }

            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'transaction_id' => $payload['transaction_id'] ?? $payment->transaction_id,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'midtrans' => [
                        'transaction_status' => $payload['transaction_status'] ?? null,
                        'payment_type' => $payload['payment_type'] ?? null,
                        'fraud_status' => $payload['fraud_status'] ?? null,
                        'gross_amount' => $payload['gross_amount'] ?? null,
                    ],
                ]),
            ]);

            $sale->update([
                'status' => 'completed',
                'transaction_date' => now(),
            ]);

            foreach ($sale->items as $item) {
                $this->stockService->performStockOut(
                    $item->product_id,
                    $item->quantity,
                    $sale->business_id,
                    $sale->user_id,
                    'Sale',
                    "Sale: {$sale->invoice_number}",
                    'sale',
                    $sale->id
                );
            }

            $this->convertReservations($sale);

            $this->accrueCustomer($sale);

            return $sale->fresh();
        });
    }

    /**
     * Mark a pending (Midtrans) sale as failed/expired/cancelled and release stock.
     */
    public function markSaleFailed(Payment $payment, string $status = 'failed', array $payload = []): Sale
    {
        return DB::transaction(function () use ($payment, $status, $payload) {
            $sale = Sale::where('id', $payment->sale_id)->lockForUpdate()->firstOrFail();

            $payment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status === 'paid') {
                return $sale;
            }

            $payment->update([
                'status' => $status,
                'transaction_id' => $payload['transaction_id'] ?? $payment->transaction_id,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'midtrans' => [
                        'transaction_status' => $payload['transaction_status'] ?? null,
                        'payment_type' => $payload['payment_type'] ?? null,
                    ],
                ]),
            ]);

            if ($sale->status === 'pending') {
                $sale->update(['status' => 'cancelled']);
            }

            $this->reservationService->releaseBySale($sale->id, $sale->business_id);

            return $sale->fresh();
        });
    }

    /**
     * Mark active reservations for the sale as converted.
     */
    protected function convertReservations(Sale $sale): void
    {
        $reservations = StockReservation::where('sale_id', $sale->id)
            ->where('status', 'active')
            ->get();

        foreach ($reservations as $reservation) {
            try {
                $this->reservationService->convert($reservation->id, $sale->business_id);
            } catch (InvalidStockReservationStateException) {
                // Reservation expired/released in the meantime - stock is settled above.
            }
        }
    }

    /**
     * Update customer spending totals and loyalty points.
     */
    protected function accrueCustomer(Sale $sale): void
    {
        if (! $sale->customer_id) {
            return;
        }

        $customer = Customer::where('business_id', $sale->business_id)->find($sale->customer_id);
        if (! $customer) {
            return;
        }

        $customer->total_spent += (float) $sale->total_amount;
        $customer->addPoints((int) floor((float) $sale->total_amount / 10000));
        $customer->save();
    }
}
