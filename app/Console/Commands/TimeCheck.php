<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TimeCheck extends Command
{
    protected $signature = 'time:check';

    protected $description = 'Controleert de servertijd t.o.v. de echte tijd (cruciaal voor de voorspel-lock van 15 min)';

    public function handle(): int
    {
        $now = now();

        $this->line('App timezone : '.config('app.timezone'));
        $this->line('App nu (UTC) : '.$now->copy()->utc()->toDateTimeString().' UTC');
        $this->line('App nu (NL)  : '.$now->copy()->timezone('Europe/Amsterdam')->toDateTimeString().' (Europe/Amsterdam)');
        $this->line('PHP systeem  : '.date('Y-m-d H:i:s P'));

        try {
            $response = Http::timeout(10)->get('https://www.google.com/generate_204');
            $dateHeader = $response->header('Date');

            if ($dateHeader) {
                $real = Carbon::parse($dateHeader); // GMT/UTC uit de HTTP-header
                $diff = $real->getTimestamp() - $now->getTimestamp();
                $abs = abs($diff);

                $this->newLine();
                $this->line('Echte tijd   : '.$real->copy()->utc()->toDateTimeString().' UTC (opgehaald van internet)');

                if ($abs <= 5) {
                    $this->info("Klok-afwijking: {$diff} sec — prima in sync ✅  De 15-minuten-lock werkt correct.");
                } elseif ($abs <= 60) {
                    $this->warn("Klok-afwijking: {$diff} sec — kleine afwijking ⚠️  Laat NTP de klok bijstellen.");
                } else {
                    $this->error("Klok-afwijking: {$diff} sec — TE GROOT ❌  De server-klok loopt niet gelijk; lock-tijden kloppen niet.");
                }
            } else {
                $this->warn('Geen Date-header ontvangen; kon de echte tijd niet bepalen.');
            }
        } catch (\Throwable $e) {
            $this->newLine();
            $this->warn('Geen internet-check mogelijk: '.$e->getMessage());
            $this->line('Vergelijk dan handmatig "App nu (UTC)" met time.is of een telefoon op UTC.');
        }

        return self::SUCCESS;
    }
}
