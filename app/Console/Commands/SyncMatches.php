<?php

namespace App\Console\Commands;

use App\Services\FootballApiService;
use Illuminate\Console\Command;

class SyncMatches extends Command
{
    protected $signature = 'matches:sync';

    protected $description = 'Synchroniseert wedstrijd-metadata (teams, datum, fase, groep) van football-data.org; uitslagen blijven onaangeroerd';

    public function handle(FootballApiService $api): int
    {
        try {
            $count = $api->syncMatches();
            $this->info("{$count} wedstrijden gesynchroniseerd.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Sync mislukt: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
