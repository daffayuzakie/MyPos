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
        Schema::table('stock_reservations', function (Blueprint $table) {
            // Change product_id FK from CASCADE to RESTRICT
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // Change sale_id FK from CASCADE to RESTRICT
            $table->dropForeign(['sale_id']);
            $table->foreign('sale_id')
                ->references('id')->on('sales')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_reservations', function (Blueprint $table) {
            // Revert product_id FK to CASCADE
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Revert sale_id FK to CASCADE
            $table->dropForeign(['sale_id']);
            $table->foreign('sale_id')
                ->references('id')->on('sales')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
};
