<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('held_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->json('items'); // serialized cart items
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->string('discount_type')->nullable(); // percentage, fixed
            $table->decimal('discount_value', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->datetime('held_at');
            $table->timestamps();
            $table->index(['business_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('held_transactions');
    }
};
