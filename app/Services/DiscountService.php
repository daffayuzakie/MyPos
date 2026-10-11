<?php

namespace App\Services;

class DiscountService
{
    public function calculateDiscount(float $subtotal, ?string $type, ?float $value): float
    {
        if (! $type || ! $value) {
            return 0;
        }

        if ($type === 'percentage') {
            return round($subtotal * ($value / 100), 2);
        }

        if ($type === 'fixed') {
            return min($value, $subtotal);
        }

        return 0;
    }
}
