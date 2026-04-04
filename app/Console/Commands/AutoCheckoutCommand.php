<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Hrm\AttendanceService;

class AutoCheckoutCommand extends Command
{
    protected $signature = 'attendance:auto-checkout';

    protected $description = 'Auto-checkout employees who forgot to check out';

    public function handle(AttendanceService $attendanceService): int
    {
        $count = $attendanceService->autoCheckoutMissing();

        $this->info("Auto-checked out {$count} employee(s).");

        return self::SUCCESS;
    }
}
