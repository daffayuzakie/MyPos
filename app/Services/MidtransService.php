<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransService
{
    protected ?string $serverKey;

    protected ?string $clientKey;

    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->isProduction = (bool) config('midtrans.is_production', false);
    }

    public function isConfigured(): bool
    {
        return ! empty($this->serverKey);
    }

    public function clientKey(): ?string
    {
        return $this->clientKey;
    }

    public function snapScriptUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    protected function snapApiUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    protected function transactionApiBase(): string
    {
        $override = config('midtrans.api_base_url');

        if (! empty($override)) {
            return rtrim($override, '/');
        }

        return $this->isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    /**
     * Fetch the current status of a transaction directly from Midtrans.
     *
     * Returns null when unconfigured, not found, or on any transport error so
     * callers can safely fall back to the webhook-driven status.
     */
    public function getTransactionStatus(string $orderId): ?array
    {
        if (! $this->isConfigured() || $orderId === '') {
            return null;
        }

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->get($this->transactionApiBase().'/v2/'.$orderId.'/status');

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Whether a transaction_status/fraud_status pair represents a settled payment.
     */
    public function isSuccessfulStatus(string $transactionStatus, ?string $fraudStatus = null): bool
    {
        if (! in_array($transactionStatus, ['capture', 'settlement'], true)) {
            return false;
        }

        return $transactionStatus === 'settlement'
            || $fraudStatus === null
            || $fraudStatus === 'accept';
    }

    /**
     * Map a failing Midtrans transaction_status to a local payment status.
     */
    public function failureStatusFor(string $transactionStatus): ?string
    {
        return match ($transactionStatus) {
            'expire' => 'expired',
            'cancel' => 'cancelled',
            'deny' => 'failed',
            default => null,
        };
    }

    /**
     * Create a Midtrans Snap transaction and return the raw response.
     *
     * @return array{token: string, redirect_url: string}
     *
     * @throws RuntimeException
     */
    public function createSnapTransaction(Sale $sale): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Midtrans server key belum dikonfigurasi.');
        }

        $sale->loadMissing(['items.product', 'customer']);

        $customerDetails = ['first_name' => $sale->customer->name ?? 'Pelanggan Umum'];
        if ($sale->customer?->phone) {
            $customerDetails['phone'] = $sale->customer->phone;
        }
        if ($sale->customer?->email) {
            $customerDetails['email'] = $sale->customer->email;
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $sale->midtransOrderId(),
                'gross_amount' => (int) round((float) $sale->total_amount),
            ],
            'customer_details' => $customerDetails,
            // Callback handled via server-side webhook.
            'credit_card' => ['secure' => (bool) config('midtrans.is_3ds', true)],
        ];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->asJson()
            ->withHeaders($this->notificationHeaders())
            ->post($this->snapApiUrl(), $payload);

        if ($response->failed()) {
            throw new RuntimeException('Gagal membuat transaksi Midtrans: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Headers that register this application's webhook on the transaction.
     *
     * @return array<string, string>
     */
    protected function notificationHeaders(): array
    {
        $notificationUrl = config('midtrans.notification_url');

        if (empty($notificationUrl)) {
            return [];
        }

        return ['X-Append-Notification' => $notificationUrl];
    }

    /**
     * Verify an incoming Midtrans webhook payload signature.
     */
    public function verifySignature(array $payload): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        if ($signature === '' || empty($this->serverKey)) {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        return hash_equals($expected, $signature);
    }
}
