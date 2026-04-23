<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            // Core Relationships
            $table->foreignId('store_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // THE FUTURE BRIDGE: Leave null for now.
            // Later, if they create an account to view their invoices, link the user_id here.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Personal / Business Info
            $table->string('name');
            $table->string('client_code')->nullable()->index();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone', 20)->nullable()->index();
            $table->string('gst_number', 30)->nullable();
            $table->string('registration_type')->default('unregistered');
            // $table->enum('registration_type', ['registered', 'unregistered', 'composition', 'overseas', 'sez'])->default('unregistered');

            // Segmented Address (Crucial for future filtering/shipping APIs)
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states');
            $table->string('zip_code', 20)->nullable();
            $table->string('country', 100)->nullable()->default('India');

            // Extras
            $table->text('notes')->nullable(); // Replaces your old 'details' column
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Replaces your old 'is_deleted' tinyint
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
