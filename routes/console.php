<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\Hrm\AnnouncementService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:auto-checkout')->dailyAt('23:00');
Schedule::command('qr-tokens:prune')->daily();
Schedule::call(function () {
    $service = app(AnnouncementService::class);
    $service->syncScheduledToPublished();
    $service->syncExpiredStatus();
})->everyMinute()
  ->name('sync-announcement-statuses')
  ->withoutOverlapping();