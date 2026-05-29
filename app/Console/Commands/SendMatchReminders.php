<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendMatchReminders extends Command
{
    protected $signature = 'reminders:send {--date= : Datum (Y-m-d) van de speeldag; standaard morgen}';

    protected $description = 'Stuurt e-mailherinneringen naar gebruikers die wedstrijden van de opgegeven dag (standaard morgen) nog niet hebben voorspeld';

    public function handle(ReminderService $reminders): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now()->addDay();

        $sent = $reminders->sendForDate($date);

        $this->info("{$sent} herinnering(en) verstuurd voor {$date->toDateString()}.");

        return self::SUCCESS;
    }
}
