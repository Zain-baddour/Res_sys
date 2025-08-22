<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands as com;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

return function (Schedule $schedule) {
    $schedule->command(com\DeleteExpiredOffers::class)->daily();
    $schedule->command(com\NotifyCloseExpiringSubscriptions::class)->dailyAt('9:00');
    $schedule->command(com\NotifyExpiredSubscriptions::class)->dailyAt('9:00');
};

