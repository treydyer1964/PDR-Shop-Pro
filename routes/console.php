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

// MRMS MESH: download MESH_Max_1440min (24-h rolling max) every 30 min — matches NOAA update frequency
Schedule::command('mesh:process')->everyThirtyMinutes();
