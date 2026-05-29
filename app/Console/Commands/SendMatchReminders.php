<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendMatchReminders extends Command
{
    protected $signature = 'reminders:send
        {--date= : Datum (Y-m-d) van de speeldag; standaard morgen}
        {--user= : Stuur alleen naar dit e-mailadres (handig om te testen)}';

    protected $description = 'Stuurt e-mailherinneringen naar gebruikers die wedstrijden van de opgegeven dag (standaard morgen) nog niet hebben voorspeld';

    public function handle(ReminderService $reminders): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now()->addDay();

        // Test-modus: alleen naar één gebruiker.
        if ($email = $this->option('user')) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                $this->error("Geen gebruiker gevonden met e-mailadres {$email}.");
                return self::FAILURE;
            }

            $sent = $reminders->sendForUser($user, $date);
            $this->info($sent
                ? "Herinnering verstuurd naar {$email} voor {$date->toDateString()}."
                : "Niets verstuurd — {$email} heeft alle wedstrijden van {$date->toDateString()} al voorspeld (of er zijn er geen).");

            return self::SUCCESS;
        }

        $sent = $reminders->sendForDate($date);
        $this->info("{$sent} herinnering(en) verstuurd voor {$date->toDateString()}.");

        return self::SUCCESS;
    }
}
