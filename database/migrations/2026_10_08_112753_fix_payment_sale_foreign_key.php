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
        Schema::table('payments', function (Blueprint $table) {
            // Revert sale_id FK to CASCADE
            $table->dropForeign(['sale_id']);
            $table->foreign('sale_id')
                ->references('id')->on('sales')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
};
