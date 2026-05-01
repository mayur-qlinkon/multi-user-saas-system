<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->boolean('is_super_admin')->default(false);
            // --- Core Auth Details ---
            $table->string('name', 100);
            $table->string('email', 150);
            $table->string('phone', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // --- Profile & Contact Details ---
            $table->string('phone_number', 20)->nullable();
            $table->text('image')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states');
            $table->string('country', 100)->default('India');
            $table->string('zip_code', 20)->nullable();

            // --- System Status ---
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
             $table->enum('user_type', ['full', 'employee'])
                  ->default('full');

            // --- Laravel Defaults ---
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'email']);
            $table->unique(['company_id', 'phone']);
        });

        // Native Laravel Password Resets (Replaces your old user_tokens & reset_code)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Native Laravel Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
