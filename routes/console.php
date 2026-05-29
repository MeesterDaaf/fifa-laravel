<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Stuurt elke dag om 18:00 herinneringen voor de wedstrijden van morgen.
Schedule::command('reminders:send')
    ->dailyAt('18:00')
    ->timezone('Europe/Amsterdam');
