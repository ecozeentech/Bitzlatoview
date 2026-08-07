<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Keep live market data fresh. Requires the Laravel scheduler cron entry to be running
// (`* * * * * php artisan schedule:run`) — see README for hosting-specific setup.
Schedule::command('market:sync-prices')->everyFiveMinutes()->withoutOverlapping();
