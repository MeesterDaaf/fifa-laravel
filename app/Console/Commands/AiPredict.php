<?php

namespace App\Console\Commands;

use App\Services\AiPredictionService;
use Illuminate\Console\Command;

class AiPredict extends Command
{
    protected $signature = 'ai:predict
        {--refresh-reasoning : Vul alleen ontbrekende onderbouwingen aan (geen nieuwe voorspellingen)}
        {--force : Genereer onderbouwingen opnieuw, ook als ze al bestaan}
        {--limit= : Beperk het aantal onderbouwingen (om te testen)}';

    protected $description = 'Laat de AI-bot open wedstrijden + (eenmalig) het toernooi voorspellen, of vul onderbouwingen aan';

    public function handle(AiPredictionService $ai): int
    {
        if ($this->option('refresh-reasoning')) {
            $limit = $this->option('limit') ? (int) $this->option('limit') : null;
            $n = $ai->backfillReasoning($limit, (bool) $this->option('force'));
            $this->info("{$n} onderbouwing(en) bijgewerkt.");
            return self::SUCCESS;
        }

        $result = $ai->run();
        $this->info("AI-bot: {$result['matches']} wedstrijd(en) voorspeld.");
        $this->info($result['tournament']
            ? 'AI-bot: toernooivoorspelling ingevuld.'
            : 'AI-bot: toernooivoorspelling stond al ingevuld.');

        return self::SUCCESS;
    }
}
