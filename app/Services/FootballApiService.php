<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\Http;

class FootballApiService
{
    private string $baseUrl = 'https://api.football-data.org/v4';

    /**
     * Haalt alle deelnemende teams + spelerslijsten op in één API-call
     * en slaat ze op. Geeft het aantal gesynchroniseerde spelers terug.
     */
    public function syncTeamsAndSquads(): array
    {
        $apiKey = config('services.football_api.key', '');
        $competitionId = config('services.football_api.competition_id', 'WC');

        $response = Http::withHeaders(['X-Auth-Token' => $apiKey])
            ->get("{$this->baseUrl}/competitions/{$competitionId}/teams");

        if (! $response->ok()) {
            throw new \Exception("Football API fout: {$response->status()} {$response->reason()}");
        }

        $teams = $response->json('teams', []);
        $teamCount = 0;
        $playerCount = 0;

        foreach ($teams as $team) {
            Team::updateOrCreate(
                ['id' => $team['id']],
                [
                    'tla'        => $team['tla'] ?? null,
                    'name'       => $team['name'],
                    'short_name' => $team['shortName'] ?? null,
                    'crest'      => $team['crest'] ?? null,
                    'area_name'  => $team['area']['name'] ?? null,
                ]
            );
            $teamCount++;

            foreach ($team['squad'] ?? [] as $player) {
                Player::updateOrCreate(
                    ['id' => $player['id']],
                    [
                        'team_id'       => $team['id'],
                        'name'          => $player['name'],
                        'position'      => $player['position'] ?? null,
                        'nationality'   => $player['nationality'] ?? null,
                        'date_of_birth' => $player['dateOfBirth'] ?? null,
                    ]
                );
                $playerCount++;
            }
        }

        return ['teams' => $teamCount, 'players' => $playerCount];
    }

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
