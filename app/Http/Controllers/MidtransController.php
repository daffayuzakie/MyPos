<?php

namespace App\Http\Controllers;

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
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = $payload['order_id'] ?? null;

        $payment = Payment::query()
            ->whereHas('sale', fn ($query) => $query->where('invoice_number', $orderId))
            ->latest()
            ->first();

        if (! $payment) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (in_array($transactionStatus, ['capture', 'settlement'], true)) {
            $isAcceptedCapture = $transactionStatus === 'settlement'
                || $fraudStatus === null
                || $fraudStatus === 'accept';

            if ($isAcceptedCapture) {
                $paymentService->markSalePaid($payment, $payload);
            }
        } elseif ($transactionStatus === 'expire') {
            $paymentService->markSaleFailed($payment, 'expired', $payload);
        } elseif ($transactionStatus === 'cancel') {
            $paymentService->markSaleFailed($payment, 'cancelled', $payload);
        } elseif ($transactionStatus === 'deny') {
            $paymentService->markSaleFailed($payment, 'failed', $payload);
        }

        return response()->json(['message' => 'OK']);
    }
}
