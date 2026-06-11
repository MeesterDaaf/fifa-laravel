<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Services\FootballApiService;
use App\Services\ScoringService;
use Illuminate\Console\Command;

class SyncLiveScores extends Command
{
    protected $signature = 'matches:sync-live';

    protected $description = 'Werkt tussenstanden van lopende wedstrijden bij via football-data.org en rondt afgelopen wedstrijden af (incl. puntenberekening)';

    public function handle(FootballApiService $api, ScoringService $scoring): int
    {
        // Geen wedstrijd rond dit tijdstip? Dan geen API-call (rate limit sparen).
        $expectingLive = Fixture::where('status', 'IN_PLAY')
            ->orWhere(fn ($q) => $q->where('status', 'SCHEDULED')
                ->where('scheduled_at', '<=', now()->addMinutes(5))
                ->where('scheduled_at', '>', now()->subHours(4)))
            ->exists();

        if (! $expectingLive) {
            $this->info('Geen wedstrijden rond dit tijdstip; sync overgeslagen.');
            return self::SUCCESS;
        }

        try {
            $result = $api->syncLiveScores();

            foreach ($result['finished'] as $fixtureId) {
                $scoring->calculateMatchPoints($fixtureId);
            }

            $this->info("{$result['updated']} wedstrijd(en) bijgewerkt, ".count($result['finished']).' afgerond.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Live-sync mislukt: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
