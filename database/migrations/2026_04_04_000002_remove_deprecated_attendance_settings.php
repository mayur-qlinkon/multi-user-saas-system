<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')->whereIn('key', [
            'attendance_checkin_start',
            'attendance_checkin_end',
            'attendance_checkout_time',
            'attendance_late_threshold_minutes',
            'attendance_min_working_hours',
            'attendance_half_day_hours',
            'attendance_office_lat',
            'attendance_office_lng',
            'attendance_gps_radius_meters',
        ])->delete();
    }

    public function down(): void {}
};
