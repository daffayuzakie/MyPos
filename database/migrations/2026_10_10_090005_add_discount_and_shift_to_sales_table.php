<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('discount_type')->nullable()->after('discount_amount'); // percentage, fixed
            $table->decimal('discount_value', 15, 2)->nullable()->after('discount_type');
            $table->string('voucher_code')->nullable()->after('discount_value');
            $table->string('payment_method')->nullable()->after('status'); // cash, qris, card, ewallet, split
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'discount_type', 'discount_value', 'voucher_code', 'payment_method']);
        });
    }
};
