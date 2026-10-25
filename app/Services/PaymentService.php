<?php

namespace App\Services;

use App\Exceptions\InvalidStockReservationStateException;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected StockService $stockService,
        protected StockReservationService $reservationService,
        protected MidtransService $midtransService
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
     * Apply a Midtrans transaction payload to its local payment/sale.
     *
     * Shared by the webhook handler and the status reconciliation so both
     * paths behave identically. Idempotent via the underlying mark methods.
     */
    public function applyMidtransStatus(Payment $payment, array $payload): Payment
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = $payload['fraud_status'] ?? null;

        if ($this->midtransService->isSuccessfulStatus($transactionStatus, $fraudStatus)) {
            if (! $this->amountMatches($payment, $payload)) {
                Log::warning('Midtrans status amount mismatch, payment not settled', [
                    'payment_id' => $payment->id,
                    'expected' => (float) $payment->amount,
                    'received' => (float) ($payload['gross_amount'] ?? 0),
                ]);

                return $payment;
            }

            $this->markSalePaid($payment, $payload);

            return $payment->fresh();
        }

        $failureStatus = $this->midtransService->failureStatusFor($transactionStatus);

        if ($failureStatus !== null) {
            $this->markSaleFailed($payment, $failureStatus, $payload);

            return $payment->fresh();
        }

        // pending / unknown status: leave the sale as-is for a later check.
        return $payment;
    }

    /**
     * Pull the current status from Midtrans and reconcile a pending payment.
     *
     * Used as a fallback when the webhook is delayed, missing, or when local
     * development cannot receive inbound notifications.
     */
    public function reconcileWithMidtrans(Payment $payment): Payment
    {
        if ($payment->provider !== 'midtrans' || $payment->status !== 'pending') {
            return $payment;
        }

        $orderId = $payment->metadata['snap_order_id'] ?? null;

        if (empty($orderId)) {
            return $payment;
        }

        $payload = $this->midtransService->getTransactionStatus($orderId);

        if ($payload === null) {
            return $payment;
        }

        $payment->update([
            'metadata' => array_merge($payment->metadata ?? [], [
                'midtrans' => array_merge($payment->metadata['midtrans'] ?? [], [
                    'last_checked_at' => now()->toIso8601String(),
                ]),
            ]),
        ]);

        return $this->applyMidtransStatus($payment, $payload);
    }

    /**
     * Guard against settling a payment whose amount does not match Midtrans.
     */
    protected function amountMatches(Payment $payment, array $payload): bool
    {
        if (! isset($payload['gross_amount'])) {
            return true;
        }

        return abs((float) $payload['gross_amount'] - (float) $payment->amount) < 0.01;
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
