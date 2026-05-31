<?php

namespace App\Console\Commands;

use App\Mail\AwaitingResultsMail;
use App\Models\Fixture;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyAwaitingResults extends Command
{
    protected $signature = 'admin:awaiting-results';

    protected $description = 'Mailt de admin(s) als er gespeelde wedstrijden op invoer wachten (speelronde voorbij)';

    public function handle(): int
    {
        $matches = Fixture::awaitingResult()->orderBy('scheduled_at')->get();

        if ($matches->isEmpty()) {
            $this->info('Geen openstaande uitslagen — geen mail verstuurd.');
            return self::SUCCESS;
        }

        $admins = User::where('is_admin', true)->where('is_bot', false)->get();
        $sent = 0;
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new AwaitingResultsMail($admin, $matches));
            $sent++;
        }

        $this->info("{$sent} admin(s) gemaild over {$matches->count()} openstaande wedstrijd(en).");
        return self::SUCCESS;
    }
}
