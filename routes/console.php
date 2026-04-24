<?php

use App\Services\Hrm\AnnouncementService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:auto-checkout')->everyThirtyMinutes();
Schedule::call(function () {
    $service = app(AnnouncementService::class);
    $service->syncScheduledToPublished();
    $service->syncExpiredStatus();
})->everyMinute()
    ->name('sync-announcement-statuses')
    ->withoutOverlapping();
