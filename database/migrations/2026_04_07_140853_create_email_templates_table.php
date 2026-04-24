<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();

            // Nullable → global template; populated → tenant-specific override.
            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            // Machine-readable event key, e.g. 'password_reset', 'otp_verification'.
            $table->string('key');

            $table->string('subject');

            // HTML body — use {{variable}} placeholders e.g. {{otp}}, {{user_name}}.
            $table->longText('body');

            $table->timestamps();

            // One template per key per company (NULL company_id = global fallback).
            $table->unique(['company_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
