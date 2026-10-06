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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->integer('quantity');
            $table->enum('reference_type', ['sale', 'purchase', 'adjustment', 'sale_cancel', 'purchase_cancel', 'refund']);
            $table->unsignedBigInteger('reference_id');
            $table->text('note');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes for inventory queries
            $table->index(['business_id', 'product_id']);
            $table->index(['business_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['business_id', 'product_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};