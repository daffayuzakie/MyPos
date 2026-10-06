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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('sku');
            $table->string('barcode')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->integer('stock');
            $table->integer('minimum_stock');
            $table->boolean('is_active');
            $table->string('image')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');

            // Composite unique constraints per business
            $table->unique(['business_id', 'sku']);
            $table->unique(['business_id', 'barcode'])->whereNotNull('barcode');
            $table->index(['business_id', 'is_active']);
            $table->index(['business_id', 'stock']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};