<?php

namespace App\Services;

use App\Models\Fixture;
use Illuminate\Support\Facades\Http;

class FootballApiService
{
    private string $baseUrl = 'https://api.football-data.org/v4';

    public function syncMatches(): int
    {
        $apiKey = config('services.football_api.key', '');
        $competitionId = config('services.football_api.competition_id', 'WC');

        $response = Http::withHeaders(['X-Auth-Token' => $apiKey])
            ->get("{$this->baseUrl}/competitions/{$competitionId}/matches");

        if (!$response->ok()) {
            throw new \Exception("Football API fout: {$response->status()} {$response->reason()}");
        }

        $matches = $response->json('matches', []);
        $count = 0;

        foreach ($matches as $match) {
            Fixture::updateOrCreate(
                ['external_id' => $match['id']],
                [
                    'home_team'      => $match['homeTeam']['name'] ?? 'TBD',
                    'away_team'      => $match['awayTeam']['name'] ?? 'TBD',
                    'home_team_code' => $match['homeTeam']['tla'] ?? 'TBD',
                    'away_team_code' => $match['awayTeam']['tla'] ?? 'TBD',
                    'scheduled_at'   => $match['utcDate'],
                    'stage'          => $match['stage'],
                    'match_group'    => $match['group'] ?? null,
                    'status'         => $this->mapStatus($match['status']),
                    'home_score'     => $match['score']['fullTime']['home'],
                    'away_score'     => $match['score']['fullTime']['away'],
                ]
            );
            $count++;
        }

        return $count;
    }

    private function mapStatus(string $apiStatus): string
    {
        return match ($apiStatus) {
            'FINISHED'       => 'FINISHED',
            'IN_PLAY', 'PAUSED' => 'IN_PLAY',
            default          => 'SCHEDULED',
        };
    }
}
