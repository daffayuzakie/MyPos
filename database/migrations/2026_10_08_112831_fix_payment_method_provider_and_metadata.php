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
            // Change method from enum to VARCHAR(50) - preserve existing values
            $table->string('method', 50)->nullable()->change();

            // Change provider from enum to VARCHAR(50) - preserve existing values
            $table->string('provider', 50)->nullable()->change();

            // Add metadata JSON column for provider response and audit
            $table->json('metadata')->nullable()->after('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Revert method from VARCHAR to enum
            $table->enum('method', ['cash', 'midtrans'])->default('cash')->change();

            // Revert provider from VARCHAR to enum
            $table->enum('provider', ['cash', 'midtrans'])->default('cash')->change();

            // Remove metadata column
            $table->dropColumn('metadata');
        });
    }
};
