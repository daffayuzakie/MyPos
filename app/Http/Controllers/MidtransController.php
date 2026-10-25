<?php

namespace App\Http\Controllers;

use App\Models\MidtransWebhookEvent;
use App\Models\Payment;
use App\Services\MidtransService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class MidtransController extends Controller
{
    /**
     * Handle Midtrans payment notification (webhook).
     */
    public function notification(Request $request, MidtransService $midtrans, PaymentService $paymentService)
    {
        $payload = $request->all();

        if (! $midtrans->verifySignature($payload)) {
            $this->logEvent(null, $payload, signatureValid: false, responseStatus: 403);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = $payload['order_id'] ?? null;

        // Match the globally unique order id stored when the pending sale was created.
        $payment = Payment::query()
            ->where('metadata->snap_order_id', $orderId)
            ->latest()
            ->first();

        // Legacy fallback for payments created before order ids were business-scoped.
        if (! $payment) {
            $payment = Payment::query()
                ->whereHas('sale', fn ($query) => $query->where('invoice_number', $orderId))
                ->latest()
                ->first();
        }

        if (! $payment) {
            // Answer 200 so Midtrans stops retrying an order we cannot resolve.
            $this->logEvent(null, $payload, signatureValid: true, responseStatus: 200);

            return response()->json(['message' => 'Order not found, ignored']);
        }

        $paymentService->applyMidtransStatus($payment, $payload);

        $this->logEvent($payment, $payload, signatureValid: true, responseStatus: 200);

        return response()->json(['message' => 'OK']);
    }

    /**
     * Persist an incoming notification for auditing and troubleshooting.
     */
    protected function logEvent(?Payment $payment, array $payload, bool $signatureValid, int $responseStatus): void
    {
        MidtransWebhookEvent::create([
            'business_id' => $payment?->business_id,
            'payment_id' => $payment?->id,
            'order_id' => $payload['order_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'signature_valid' => $signatureValid,
            'response_status' => $responseStatus,
            'payload' => $payload,
        ]);
    }
}
