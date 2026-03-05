<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hail Tracker: re-fetch today's SPC reports every hour, then immediately check alerts
Schedule::command('hail:fetch-reports')->hourly()
    ->then(fn() => Artisan::call('hail:check-alerts'));

// MRMS MESH: process latest GRIB2 frame every 10 minutes (builds daily max swath)
Schedule::command('mesh:process')->everyTenMinutes();
