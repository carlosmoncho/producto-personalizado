<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============ SCHEDULED TASKS ============

// Run PageSpeed audit daily at 6 AM (when traffic is low)
Schedule::command('pagespeed:audit --both')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/pagespeed.log'))
    ->onSuccess(function () {
        info('PageSpeed audit completed successfully');
    })
    ->onFailure(function () {
        logger()->error('PageSpeed audit failed');
    });

// Run quick mobile-only audit every 6 hours
Schedule::command('pagespeed:audit')
    ->everySixHours()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/pagespeed.log'));
