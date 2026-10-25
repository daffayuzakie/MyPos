<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        protected Sale $saleModel,
        protected SaleItem $saleItemModel,
        protected Product $productModel,
        protected Payment $paymentModel,
        protected StockService $stockService,
        protected StockReservationService $reservationService,
        protected DiscountService $discountService
    ) {}

    /**
     * Complete an immediate (cash) sale checkout.
     *
     * @param  int  $receivedCash
     *
     * @throws \InvalidArgumentException
     * @throws InsufficientStockException
     */
    public function checkout(
        int $businessId,
        int $userId,
        array $items,
        float $receivedCash,
        ?string $notes = null,
        ?string $paymentMethod = 'cash',
        ?int $customerId = null,
        ?string $discountType = null,
        ?float $discountValue = null,
        ?string $voucherCode = null
    ): array {
        if (empty($items)) {
            throw new \InvalidArgumentException('Cart cannot be empty.');
        }

        if ($receivedCash < 0) {
            throw new \InvalidArgumentException('Received cash cannot be negative.');
        }

        return DB::transaction(function () use (
            $businessId, $userId, $items, $receivedCash, $notes,
            $paymentMethod, $customerId, $discountType,
            $discountValue, $voucherCode
        ) {
            ['items' => $validatedItems, 'subtotal' => $subtotal] = $this->prepareItems($businessId, $items);

            $amounts = $this->calculateAmounts($businessId, $subtotal, $discountType, $discountValue);
            $discountAmount = $amounts['discount_amount'];
            $taxPercentage = $amounts['tax_percentage'];
            $taxAmount = $amounts['tax_amount'];
            $totalAmount = $amounts['total_amount'];

            $isCash = ($paymentMethod ?? 'cash') === 'cash';

            // Validate received cash for cash payments
            if ($isCash && $receivedCash < $totalAmount) {
                throw new \InvalidArgumentException(
                    "Insufficient cash. Total: {$totalAmount}, Received: {$receivedCash}"
                );
            }

            $change = $isCash ? max(0, $receivedCash - $totalAmount) : 0;

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($businessId);

            // Create Sale
            $sale = $this->saleModel->create([
                'business_id' => $businessId,
                'user_id' => $userId,
                'customer_id' => $customerId,
                'invoice_number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'voucher_code' => $voucherCode,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'completed',
                'payment_method' => $paymentMethod ?? 'cash',
                'transaction_date' => now(),
                'notes' => $notes,
            ]);

            // Process each item
            foreach ($validatedItems as $item) {
                $this->stockService->performStockOut(
                    $item['product_id'],
                    $item['quantity'],
                    $businessId,
                    $userId,
                    'Sale',
                    "Sale: {$sale->invoice_number}",
                    'sale',
                    $sale->id
                );

                $this->saleItemModel->create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => 0, // Item-level discount if any
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // Create Payment
            $payment = $this->paymentModel->create([
                'business_id' => $businessId,
                'sale_id' => $sale->id,
                'method' => $paymentMethod ?? 'cash',
                'provider' => $paymentMethod ?? 'cash',
                'amount' => $totalAmount,
                'status' => 'paid',
                'paid_at' => now(),
                'metadata' => [
                    'received_cash' => $receivedCash,
                    'change' => $change,
                ],
            ]);

            // Update Customer
            $this->accrueCustomer($businessId, $customerId, $totalAmount);

            return [
                'sale' => $sale->fresh(),
                'change' => $change,
                'payment' => $payment,
            ];
        });
    }

    /**
     * Create a pending (Midtrans) sale, reserving stock until payment is settled.
     *
     * Stock is NOT decremented here; it is settled when the Midtrans webhook
     * confirms payment via PaymentService::markSalePaid().
     *
     * @throws \InvalidArgumentException
     * @throws InsufficientStockException
     */
    public function createPendingSale(
        int $businessId,
        int $userId,
        array $items,
        ?int $customerId = null,
        ?string $discountType = null,
        ?float $discountValue = null,
        ?string $voucherCode = null,
        int $reservationMinutes = 15
    ): array {
        if (empty($items)) {
            throw new \InvalidArgumentException('Cart cannot be empty.');
        }

        return DB::transaction(function () use (
            $businessId, $userId, $items, $customerId,
            $discountType, $discountValue, $voucherCode, $reservationMinutes
        ) {
            ['items' => $validatedItems, 'subtotal' => $subtotal] = $this->prepareItems($businessId, $items);

            $amounts = $this->calculateAmounts($businessId, $subtotal, $discountType, $discountValue);

            $invoiceNumber = $this->generateInvoiceNumber($businessId);

            $sale = $this->saleModel->create([
                'business_id' => $businessId,
                'user_id' => $userId,
                'customer_id' => $customerId,
                'invoice_number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'discount_amount' => $amounts['discount_amount'],
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'voucher_code' => $voucherCode,
                'tax_amount' => $amounts['tax_amount'],
                'total_amount' => $amounts['total_amount'],
                'status' => 'pending',
                'payment_method' => 'midtrans',
                'transaction_date' => now(),
            ]);

            foreach ($validatedItems as $item) {
                $this->saleItemModel->create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => 0,
                    'subtotal' => $item['subtotal'],
                ]);

                $this->reservationService->create(
                    $businessId,
                    $sale->id,
                    $item['product_id'],
                    $item['quantity'],
                    now()->addMinutes($reservationMinutes)
                );
            }

            $payment = $this->paymentModel->create([
                'business_id' => $businessId,
                'sale_id' => $sale->id,
                'method' => 'midtrans',
                'provider' => 'midtrans',
                'amount' => $amounts['total_amount'],
                'status' => 'pending',
                'metadata' => [
                    'snap_order_id' => $sale->midtransOrderId(),
                ],
            ]);

            return [
                'sale' => $sale->fresh(),
                'payment' => $payment,
                'total_amount' => $amounts['total_amount'],
            ];
        });
    }

    /**
     * Validate cart items, lock product rows and return line items + subtotal.
     *
     * @throws \InvalidArgumentException
     * @throws InsufficientStockException
     */
    protected function prepareItems(int $businessId, array $items): array
    {
        usort($items, fn ($a, $b) => $a['product_id'] <=> $b['product_id']);

        $subtotal = 0;
        $validatedItems = [];

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = (int) $item['quantity'];

            if ($quantity <= 0) {
                throw new \InvalidArgumentException('Quantity must be positive.');
            }

            $product = $this->productModel
                ->where('business_id', $businessId)
                ->where('id', $productId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $product->is_active) {
                throw new \InvalidArgumentException("Product {$product->name} is not active.");
            }

            $available = $this->reservationService->availableStock($businessId, $productId);
            if ($quantity > $available) {
                throw new InsufficientStockException(
                    "Insufficient stock for {$product->name}. Available: {$available}, Requested: {$quantity}"
                );
            }

            $unitPrice = $product->selling_price;
            $lineSubtotal = $quantity * $unitPrice;
            $subtotal += $lineSubtotal;

            $validatedItems[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $lineSubtotal,
                'product' => $product,
            ];
        }

        return ['items' => $validatedItems, 'subtotal' => $subtotal];
    }

    /**
     * Calculate discount, tax and total for a subtotal.
     */
    protected function calculateAmounts(int $businessId, float $subtotal, ?string $discountType, ?float $discountValue): array
    {
        $discountAmount = $this->discountService->calculateDiscount($subtotal, $discountType, $discountValue);
        $discountedSubtotal = max(0, $subtotal - $discountAmount);

        $business = Business::findOrFail($businessId);
        $taxPercentage = $business->tax_percentage ?? 0;
        $taxAmount = round($discountedSubtotal * $taxPercentage / 100, 2);

        return [
            'discount_amount' => $discountAmount,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'total_amount' => $discountedSubtotal + $taxAmount,
        ];
    }

    /**
     * Update customer spending totals and loyalty points.
     */
    protected function accrueCustomer(int $businessId, ?int $customerId, float $totalAmount): void
    {
        if (! $customerId) {
            return;
        }

        $customer = Customer::where('business_id', $businessId)->find($customerId);
        if (! $customer) {
            return;
        }

        $customer->total_spent += $totalAmount;
        $customer->addPoints((int) floor($totalAmount / 10000));
        $customer->save();
    }

    private function generateInvoiceNumber(int $businessId): string
    {
        $business = Business::where('id', $businessId)
            ->lockForUpdate()
            ->firstOrFail();

        $prefix = $business->invoice_prefix ?? 'INV';
        $sequence = $business->invoice_sequence ?? 1;

        // Ensure the sequence is never duplicate
        while (Sale::where('business_id', $businessId)->where('invoice_number', "{$prefix}-{$sequence}")->exists()) {
            $sequence++;
        }

        $invoiceNumber = "{$prefix}-{$sequence}";

        $business->invoice_sequence = $sequence + 1;
        $business->save();

        return $invoiceNumber;
    }
}
