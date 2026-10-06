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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('refund_payment_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('method', ['cash', 'midtrans', 'original_method']);
            $table->enum('status', ['pending', 'completed', 'failed']);
            $table->text('reason');
            $table->timestamp('refunded_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->foreign('refund_payment_id')->references('id')->on('payments')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');

            // Indexes for refund tracking
            $table->index(['business_id', 'status']);
            $table->index(['sale_id']);
            $table->index(['payment_id']);
            $table->index(['refund_payment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};