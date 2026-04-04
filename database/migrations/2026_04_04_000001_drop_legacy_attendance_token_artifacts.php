<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $attendanceColumns = array_values(array_filter([
            Schema::hasColumn('attendances', 'check_in_qr_token') ? 'check_in_qr_token' : null,
            Schema::hasColumn('attendances', 'check_out_qr_token') ? 'check_out_qr_token' : null,
        ]));

        if ($attendanceColumns !== []) {
            Schema::table('attendances', function (Blueprint $table) use ($attendanceColumns) {
                $table->dropColumn($attendanceColumns);
            });
        }

        if (Schema::hasColumn('attendance_logs', 'qr_token')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->dropColumn('qr_token');
            });
        }

        Schema::dropIfExists('qr_tokens');
    }

    public function down(): void
    {
        if (! Schema::hasTable('qr_tokens')) {
            Schema::create('qr_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
                $table->string('token', 64)->unique();
                $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('expires_at');
                $table->boolean('is_used')->default(false);
                $table->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
                $table->index(['token', 'is_used', 'expires_at']);
                $table->index(['company_id', 'store_id', 'expires_at']);
            });
        }

        if (! Schema::hasColumn('attendances', 'check_in_qr_token')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->string('check_in_qr_token', 64)->nullable()->after('check_in_lng');
            });
        }

        if (! Schema::hasColumn('attendances', 'check_out_qr_token')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->string('check_out_qr_token', 64)->nullable()->after('check_out_lng');
            });
        }

        if (! Schema::hasColumn('attendance_logs', 'qr_token')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->string('qr_token', 64)->nullable()->after('longitude');
            });
        }
    }
};
