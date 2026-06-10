<?php

namespace App\Console\Commands;

use App\Mail\TournamentDeadlineMail;
use App\Models\TournamentPrediction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyTournamentDeadline extends Command
{
    protected $signature = 'toernooi:notify-deadline
        {--user= : Stuur alleen naar dit e-mailadres (handig om te testen)}
        {--dry-run : Laat zien wie een mail zou krijgen, zonder te versturen}';

    protected $description = 'Mailt alle deelnemers dat de toernooivoorspelling sluit bij de eerste wedstrijd';

    public function handle(): int
    {
        $deadline = TournamentPrediction::deadline();

        $query = User::where('is_bot', false);
        if ($email = $this->option('user')) {
            $query->where('email', $email);
        }
        $users = $query->get();

        if ($users->isEmpty()) {
            $this->error($this->option('user')
                ? "Geen gebruiker gevonden met e-mailadres {$this->option('user')}."
                : 'Geen deelnemers gevonden.');
            return self::FAILURE;
        }

        $dryRun = $this->option('dry-run');
        $sent = 0;

        foreach ($users as $user) {
            $missing = $this->missingFor($user);

            if ($dryRun) {
                $this->line(sprintf(
                    '— %s <%s> | ontbreekt: %s',
                    $user->name,
                    $user->email,
                    $missing ? implode(', ', $missing) : 'niets',
                ));
                continue;
            }

            Mail::to($user->email)->send(new TournamentDeadlineMail($user, $deadline, $missing));
            $sent++;
        }

        if ($dryRun) {
            $this->info("{$users->count()} deelnemer(s) zouden een mail krijgen (dry-run, niets verstuurd).");
        } else {
            $this->info("{$sent} deelnemer(s) gemaild over de toernooi-deadline.");
        }

        return self::SUCCESS;
    }

    /**
     * Geeft de nog niet ingevulde onderdelen van de toernooivoorspelling terug.
     *
     * @return array<int, string>
     */
    private function missingFor(User $user): array
    {
        $tp = TournamentPrediction::where('user_id', $user->id)->first();

        $missing = [];
        if (! filled($tp?->champion)) {
            $missing[] = '🏆 Winnaar';
        }
        if (! filled($tp?->top_scorer)) {
            $missing[] = '🥇 Topscorer';
        }
        if ($tp?->total_yellow_cards === null) {
            $missing[] = '🟨 Totaal gele kaarten';
        }
        if ($tp?->total_red_cards === null) {
            $missing[] = '🟥 Totaal rode kaarten';
        }

        return $missing;
    }
}
