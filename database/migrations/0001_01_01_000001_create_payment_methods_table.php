<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 50)->unique();       // cash, upi, card, bank_transfer, cheque, emi
            $table->string('label', 100);               // Display: "Cash", "UPI", "Credit/Debit Card"
            $table->string('gateway', 50)->nullable();  // razorpay, stripe, payu — null for offline
            $table->boolean('is_online')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });      
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};