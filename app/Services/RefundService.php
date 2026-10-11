<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function __construct(protected StockService $stockService) {}

    public function processRefund(int $businessId, int $userId, int $saleId, array $items, string $reason): array
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('Tidak ada item yang direfund.');
        }

        return DB::transaction(function () use ($businessId, $userId, $saleId, $items, $reason) {
            $sale = Sale::where('business_id', $businessId)->findOrFail($saleId);

            if ($sale->status === 'refunded') {
                throw new \InvalidArgumentException('Transaksi ini sudah direfund sepenuhnya.');
            }

            $refundTotal = 0;
            $refundItemsData = [];

            foreach ($items as $item) {
                $saleItem = SaleItem::where('sale_id', $saleId)
                    ->where('id', $item['sale_item_id'])
                    ->firstOrFail();

                $quantity = (int) ($item['quantity'] ?? 0);
                $alreadyRefunded = (int) RefundItem::where('sale_item_id', $saleItem->id)->sum('quantity');
                $available = $saleItem->quantity - $alreadyRefunded;

                if ($quantity < 1) {
                    throw new \InvalidArgumentException('Jumlah refund tidak valid.');
                }

                if ($quantity > $available) {
                    throw new \InvalidArgumentException(
                        "Jumlah refund untuk produk #{$saleItem->product_id} melebihi jumlah yang tersedia (maksimal {$available})."
                    );
                }

                $subtotal = $quantity * $saleItem->unit_price;
                $refundTotal += $subtotal;

                $refundItemsData[] = [
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'quantity' => $quantity,
                    'unit_price' => $saleItem->unit_price,
                    'subtotal' => $subtotal,
                    'reason' => $reason,
                ];

                $this->stockService->performStockIn(
                    $saleItem->product_id,
                    $quantity,
                    $businessId,
                    $userId,
                    'Refund',
                    "Refund for sale {$sale->invoice_number}",
                    'refund',
                    $sale->id
                );
            }

            $originalPayment = $sale->latestPayment;

            $payment = Payment::create([
                'business_id' => $businessId,
                'sale_id' => $sale->id,
                'method' => $originalPayment->method ?? 'cash',
                'provider' => $originalPayment->provider ?? 'cash',
                'amount' => -$refundTotal,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $refund = Refund::create([
                'business_id' => $businessId,
                'sale_id' => $sale->id,
                'payment_id' => $originalPayment?->id,
                'refund_payment_id' => $payment->id,
                'amount' => $refundTotal,
                'method' => $originalPayment->method ?? 'cash',
                'status' => 'completed',
                'reason' => $reason,
                'refunded_at' => now(),
                'created_by' => $userId,
            ]);

            foreach ($refundItemsData as $data) {
                $data['refund_id'] = $refund->id;
                RefundItem::create($data);
            }

            $fullyRefunded = true;
            foreach (SaleItem::where('sale_id', $saleId)->get() as $saleItem) {
                $refunded = (int) RefundItem::where('sale_item_id', $saleItem->id)->sum('quantity');
                if ($refunded < $saleItem->quantity) {
                    $fullyRefunded = false;
                    break;
                }
            }

            if ($fullyRefunded) {
                $sale->update(['status' => 'refunded']);
            }

            return ['refund' => $refund, 'payment' => $payment];
        });
    }
}
