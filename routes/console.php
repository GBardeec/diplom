<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:test')
    ->cron('15 3 */3 * *')
    ->timezone('Europe/Samara')
    ->withoutOverlapping(180)
    ->appendOutputTo(storage_path('logs/vacancy-import.log'));
