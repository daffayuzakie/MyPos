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
        Schema::create('midtrans_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('transaction_status')->nullable();
            $table->boolean('signature_valid')->default(false);
            $table->unsignedSmallInteger('response_status')->default(200);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('business_id');
            $table->index('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('midtrans_webhook_events');
    }
};
