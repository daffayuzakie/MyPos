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
        if (! Schema::hasTable('businesses')) {
            Schema::create('businesses', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('logo')->nullable();
                $table->decimal('tax_percentage', 5, 2)->default(0);
                $table->string('invoice_prefix')->default('INV');
                $table->integer('invoice_sequence')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
