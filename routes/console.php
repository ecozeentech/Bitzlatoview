<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Keep live market data fresh. Requires the Laravel scheduler cron entry to be running
// (`* * * * * php artisan schedule:run`) — see README for hosting-specific setup. Runs every
// minute (the finest granularity cron supports) so every price shown across the platform is
// never more than ~60s behind the market, on top of the frontend's own polling refresh.
Schedule::command('market:sync-prices')->everyMinute()->withoutOverlapping();
