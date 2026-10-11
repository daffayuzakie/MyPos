<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        // Semua data default berasal dari business yang sama.
        $business = Business::factory()->create();

        // Gunakan stock awal yang cukup supaya semua jenis movement
        // (IN, OUT, adjustment positif/negatif) bisa dibuat valid.
        $product = Product::factory()->create([
            'business_id' => $business->id,
            'stock' => 100,
        ]);

        $user = User::factory()->create([
            'business_id' => $business->id,
        ]);

        $type = $this->faker->randomElement([
            'in',
            'out',
            'adjustment',
        ]);

        $stockBefore = $product->stock;

        if ($type === 'in') {
            // IN harus selalu positif.
            $quantity = $this->faker->numberBetween(1, 100);

            $stockAfter = $stockBefore + $quantity;
        } elseif ($type === 'out') {
            // OUT harus positif dan tidak boleh melebihi stock.
            $quantity = $this->faker->numberBetween(1, $stockBefore);

            $stockAfter = $stockBefore - $quantity;
        } else {
            // Adjustment menggunakan signed quantity.
            //
            // Positif = menambah stock.
            // Negatif = mengurangi stock.
            // Tidak boleh 0.
            // Tidak boleh menghasilkan stock negatif.
            $minAdjustment = -min(50, $stockBefore);
            $maxAdjustment = 50;

            do {
                $quantity = $this->faker->numberBetween(
                    $minAdjustment,
                    $maxAdjustment
                );
            } while ($quantity === 0);

            $stockAfter = $stockBefore + $quantity;
        }

        /*
         * PENTING:
         * Factory TIDAK mengubah products.stock.
         *
         * Perubahan stock hanya boleh melalui StockService.
         * Factory hanya membuat data movement yang valid.
         */

        return [
            'business_id' => $business->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => $type,
            'quantity' => $quantity,
            'reference_type' => null,
            'reference_id' => null,
            'reason' => $this->faker->sentence(),
            'note' => $this->faker->optional()->sentence(),
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
        ];
    }
}
