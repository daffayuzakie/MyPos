<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add reason as nullable
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('reason', 255)->nullable()->after('type');
        });

        // Backfill existing rows
        DB::table('stock_movements')->whereNull('reason')->update(['reason' => 'Legacy stock movement']);

        // Step 3: Make reason NOT NULL
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('reason', 255)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
