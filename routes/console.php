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

// Laat de AI-bot elke dag om 12:00 nieuwe open wedstrijden voorspellen
// (ruim vóór de deadline, en knock-outs zodra de teams bekend zijn).
Schedule::command('ai:predict')
    ->dailyAt('12:00')
    ->timezone('Europe/Amsterdam');

// Mailt de admin elke ochtend om 09:00 als er gespeelde wedstrijden op invoer wachten.
Schedule::command('admin:awaiting-results')
    ->dailyAt('09:00')
    ->timezone('Europe/Amsterdam');
