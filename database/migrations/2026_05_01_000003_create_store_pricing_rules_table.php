<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Table: ocr_scans
     * Purpose: Stores every OCR scan attempted by a tenant user.
     *          The `extracted_data` JSON column holds the raw parsed fields
     *          so we never lose OCR output, even if the user edits before saving.
     */
    public function up(): void
    {
         Schema::create('store_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_sku_id')->constrained('product_skus')->cascadeOnDelete();

            // Override price for this specific branch
            $table->decimal('override_price', 12, 2)->nullable();
            $table->decimal('override_mrp', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['store_id', 'product_sku_id']); // one rule per SKU per store
            $table->index(['company_id', 'store_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_pricing_rules');
    }
};