<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            // Persistent temp directory path for ZIP-based imports (product images).
            // Reused across chunked process requests; cleaned after the final chunk.
            $table->string('temp_path')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn('temp_path');
        });
    }
};
