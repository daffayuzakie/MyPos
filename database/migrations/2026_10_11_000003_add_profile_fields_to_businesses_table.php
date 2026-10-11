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
        Schema::table('businesses', function (Blueprint $table) {
            if (! Schema::hasColumn('businesses', 'type')) {
                $table->string('type')->nullable()->after('name');
            }
            if (! Schema::hasColumn('businesses', 'timezone')) {
                $table->string('timezone')->default('Asia/Jakarta')->after('type');
            }
            if (! Schema::hasColumn('businesses', 'payment_methods')) {
                $table->json('payment_methods')->nullable()->after('tax_percentage');
            }
            if (! Schema::hasColumn('businesses', 'onboarded_at')) {
                $table->timestamp('onboarded_at')->nullable()->after('payment_methods');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['type', 'timezone', 'payment_methods', 'onboarded_at']);
        });
    }
};
