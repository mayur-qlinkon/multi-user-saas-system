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
         Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('transfer_number', 30)->unique();

            $table->foreignId('from_store_id')->constrained('stores');
            $table->foreignId('to_store_id')->constrained('stores');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses');

            $table->enum('status', ['draft','in_transit','received','cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['company_id', 'from_store_id', 'status']);
            $table->index(['company_id', 'to_store_id', 'status']);
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_sku_id')->constrained('product_skus');
            $table->foreignId('batch_id')->nullable()->constrained('product_batches');
            $table->decimal('qty_sent', 10, 3);
            $table->decimal('qty_received', 10, 3)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
    }
};