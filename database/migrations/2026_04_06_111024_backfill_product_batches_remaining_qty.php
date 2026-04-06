<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add remaining_qty column if it does not exist, then backfill it
     * with the original purchase qty for all existing batch records.
     *
     * Safe rule: records created before this feature was implemented have
     * remaining_qty = 0 and qty > 0, so we restore remaining = original.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('product_batches', 'remaining_qty')) {
            Schema::table('product_batches', function (Blueprint $table) {
                $table->integer('remaining_qty')->default(0)->after('qty');
            });
        }

        DB::statement('UPDATE product_batches SET remaining_qty = qty WHERE remaining_qty = 0 AND qty > 0');
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropColumn('remaining_qty');
        });
    }
};
