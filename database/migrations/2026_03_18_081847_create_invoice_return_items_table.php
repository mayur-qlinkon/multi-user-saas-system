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
            // 2. INVOICE RETURN ITEMS TABLE (Credit Notes Ledger lines)
        Schema::create('invoice_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_return_id')->constrained()->cascadeOnDelete();
            
            // 🌟 Crucial: Link to the exact line item on the original invoice
            $table->foreignId('invoice_item_id')->constrained()->restrictOnDelete();
            
            // Snapshots
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_sku_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();

            $table->string('product_name');
            $table->string('hsn_code', 50)->nullable();
            
            // 🌟 This is the RETURNED quantity, not the original quantity
            $table->decimal('quantity', 10, 4);
            $table->decimal('unit_price', 15, 4); 
            $table->boolean('is_restocked')->default(true);
            
            $table->enum('tax_type', ['inclusive', 'exclusive'])->default('exclusive');
            $table->enum('discount_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('discount_amount', 15, 4)->default(0);

            $table->decimal('taxable_value', 15, 4)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            
            $table->decimal('cgst_amount', 15, 4)->default(0);
            $table->decimal('sgst_amount', 15, 4)->default(0);
            $table->decimal('igst_amount', 15, 4)->default(0);
            $table->decimal('tax_amount', 15, 4)->default(0);
            
            $table->decimal('total_amount', 15, 4)->default(0); 

            $table->timestamps();
        });   
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_return_items');
    }
};
