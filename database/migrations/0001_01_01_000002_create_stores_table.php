<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('stores', function (Blueprint $table) {
            $table->id();   
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); 
                        
            // --- 🏠 Core Branding ---
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('upi_id')->nullable();
            $table->string('logo')->nullable();
            $table->string('signature')->nullable();

            // --- 🇮🇳 Compliance & Regional ---
            $table->string('gst_number', 15)->nullable(); // GSTIN is always 15 chars
            $table->string('currency', 10)->default('INR');
            
            // --- 📍 Location (The GST Foundation) ---
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('country', 100)->default('India');
            $table->foreignId('state_id')->nullable()->constrained('states');                    
            $table->decimal('office_lat', 10, 7)->nullable();
            $table->decimal('office_lng', 10, 7)->nullable();
            $table->unsignedInteger('gps_radius_meters')->nullable();

            // --- 📄 Billing Customization (Future-Safe) ---
            // Every store might want its own sequence: "ST1-INV-001" vs "BOM-INV-001"
            $table->string('invoice_prefix', 10)->nullable();
            $table->string('purchase_prefix', 10)->nullable();
            $table->integer('next_invoice_number')->default(1);
            
            // --- Status & Timestamps ---
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
       
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};