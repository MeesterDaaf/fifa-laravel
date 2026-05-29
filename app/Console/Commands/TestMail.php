<?php

namespace App\Console\Commands;

use App\Mail\MatchReminderMail;
use App\Models\Fixture;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    protected $signature = 'mail:test {email : Het e-mailadres waar de testmail heen gaat}';

    protected $description = 'Stuurt één voorbeeld-herinneringsmail om de mailconfiguratie (SMTP) te testen';

    public function handle(): int
    {
        $email = $this->argument('email');

        // Gebruik echte aankomende wedstrijden; val terug op een voorbeeld als die er niet zijn.
        $matches = Fixture::where('status', 'SCHEDULED')
            ->orderBy('scheduled_at')
            ->limit(3)
            ->get();

        if ($matches->isEmpty()) {
            $matches = collect([
                new Fixture([
                    'home_team' => 'Nederland', 'away_team' => 'België',
                    'home_team_code' => 'NED', 'away_team_code' => 'BEL',
                    'scheduled_at' => now()->addDay()->setTime(20, 0),
                    'stage' => 'GROUP_STAGE', 'status' => 'SCHEDULED',
                ]),
            ]);
        }

        $user = new User(['name' => 'Test', 'email' => $email]);
        $date = $matches->first()->scheduled_at;

        $this->info("Versturen naar {$email} via mailer '" . config('mail.default') . "' ...");

        try {
            Mail::to($email)->send(new MatchReminderMail($user, $matches, $date));
            $this->info('✅ Testmail verzonden. Check je inbox (of storage/logs/laravel.log als MAIL_MAILER=log).');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Versturen mislukt: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
