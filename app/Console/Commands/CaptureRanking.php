<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Services\ScoringService;
use Illuminate\Console\Command;

class CaptureRanking extends Command
{
    protected $signature = 'ranking:capture';

    protected $description = 'Legt de huidige ranglijst vast als ijkpunt voor de stijgers/dalers (▲/▼)';

    public function handle(ScoringService $scoring): int
    {
        // Niet vastleggen midden in een wedstrijd: dan zou het ijkpunt een
        // tussenstand bevatten en kloppen de stijgers/dalers van die dag niet.
        if (Fixture::live()->exists()) {
            $this->info('Er is een wedstrijd bezig; vastleggen overgeslagen.');
            return self::SUCCESS;
        }

        $scoring->captureRanking();
        $this->info('Ranglijst vastgelegd als ijkpunt.');
        return self::SUCCESS;
    }
}
