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
// Output gaat naar een logbestand zodat je op de server kunt zien dat (en wat)
// de sync elke minuut doet: tail -f storage/logs/live-sync.log
Schedule::command('matches:sync-live')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/live-sync.log'));

// Het ranglijst-ijkpunt (voor de ▲/▼-pijltjes) wordt niet meer op een vast
// tijdstip vastgelegd: dat wiste de pijltjes elke ochtend, terwijl de
// (Noord-Amerikaanse nacht)wedstrijden net daarvoor waren afgelopen. Het
// ijkpunt wordt nu vastgelegd op het moment dat een wedstrijd wordt afgerond
// (in matches:sync-live en bij handmatige invoer), vlak vóór de punten-
// berekening — de pijltjes blijven staan tot de volgende wedstrijd afloopt.
// Handmatig kan nog steeds: php artisan ranking:capture of de admin-knop.

// Laat de AI-bot elke dag om 12:00 nieuwe open wedstrijden voorspellen
// (ná de sync, dus inclusief net-bekende knock-outteams).
Schedule::command('ai:predict')
    ->dailyAt('12:00')
    ->timezone('Europe/Amsterdam');

// Mailt de admin elke ochtend om 09:00 als er gespeelde wedstrijden op invoer wachten.
Schedule::command('admin:awaiting-results')
    ->dailyAt('09:00')
    ->timezone('Europe/Amsterdam');
