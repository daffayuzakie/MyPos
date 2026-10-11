<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Make reference_type and reference_id nullable for manual stock movements
            $table->enum('reference_type', ['sale', 'purchase', 'adjustment', 'sale_cancel', 'purchase_cancel', 'refund'])->nullable()->change();
            $table->unsignedBigInteger('reference_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->enum('reference_type', ['sale', 'purchase', 'adjustment', 'sale_cancel', 'purchase_cancel', 'refund'])->nullable(false)->change();
            $table->unsignedBigInteger('reference_id')->nullable(false)->change();
        });
    }
};
