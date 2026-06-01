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

// Synchroniseert elke ochtend om 06:00 de wedstrijden (teams/datum/fase) —
// zo komen knock-outteams vanzelf binnen. Uitslagen blijven handmatig.
Schedule::command('matches:sync')
    ->dailyAt('06:00')
    ->timezone('Europe/Amsterdam');

// Laat de AI-bot elke dag om 12:00 nieuwe open wedstrijden voorspellen
// (ná de sync, dus inclusief net-bekende knock-outteams).
Schedule::command('ai:predict')
    ->dailyAt('12:00')
    ->timezone('Europe/Amsterdam');

// Mailt de admin elke ochtend om 09:00 als er gespeelde wedstrijden op invoer wachten.
Schedule::command('admin:awaiting-results')
    ->dailyAt('09:00')
    ->timezone('Europe/Amsterdam');
