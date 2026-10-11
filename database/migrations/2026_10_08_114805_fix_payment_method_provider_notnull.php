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
        Schema::table('payments', function (Blueprint $table) {
            // Make method NOT NULL with default 'cash' (preserve existing values)
            $table->string('method', 50)->default('cash')->nullable(false)->change();

            // Make provider NOT NULL with default 'cash' (preserve existing values)
            $table->string('provider', 50)->default('cash')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Revert method to nullable varchar
            $table->string('method', 50)->nullable()->default('cash')->change();

            // Revert provider to nullable varchar
            $table->string('provider', 50)->nullable()->default('cash')->change();
        });
    }
};
