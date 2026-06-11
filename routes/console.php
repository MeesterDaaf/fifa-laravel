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
// zo komen knock-outteams vanzelf binnen.
Schedule::command('matches:sync')
    ->dailyAt('06:00')
    ->timezone('Europe/Amsterdam');

// Live-sync: elke minuut tussenstanden bijwerken en afgelopen wedstrijden
// afronden (incl. puntenberekening). Doet alleen een API-call als er rond
// dat moment een wedstrijd is, dus buiten wedstrijden kost dit niets.
Schedule::command('matches:sync-live')
    ->everyMinute()
    ->withoutOverlapping();

// Legt elke ochtend de ranglijst vast als ijkpunt voor de ▲/▼-pijltjes.
// Om 09:00 NL zijn ook de laatste (Noord-Amerikaanse nacht)wedstrijden klaar;
// de pijltjes tonen daarna de beweging door de wedstrijden van die dag.
Schedule::command('ranking:capture')
    ->dailyAt('09:00')
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
