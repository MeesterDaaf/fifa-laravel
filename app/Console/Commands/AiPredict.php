<?php

namespace App\Console\Commands;

use App\Services\AiPredictionService;
use Illuminate\Console\Command;

class AiPredict extends Command
{
    protected $signature = 'ai:predict';

    protected $description = 'Laat de AI-bot alle open wedstrijden en (eenmalig) de toernooivoorspelling invullen';

    public function handle(AiPredictionService $ai): int
    {
        $result = $ai->run();

        $this->info("AI-bot: {$result['matches']} wedstrijd(en) voorspeld.");
        $this->info($result['tournament']
            ? 'AI-bot: toernooivoorspelling ingevuld.'
            : 'AI-bot: toernooivoorspelling stond al ingevuld.');

        return self::SUCCESS;
    }
}
