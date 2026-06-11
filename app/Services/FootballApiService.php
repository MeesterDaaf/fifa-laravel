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
            // Alleen wedstrijd-metadata (teams, datum, fase, groep). Status en
            // uitslagen blijven onaangeroerd — die voert de admin handmatig in,
            // zodat de puntenberekening klopt en niets wordt overschreven.
            $meta = [
                'home_team'      => $match['homeTeam']['name'] ?? 'TBD',
                'away_team'      => $match['awayTeam']['name'] ?? 'TBD',
                'home_team_code' => $match['homeTeam']['tla'] ?? 'TBD',
                'away_team_code' => $match['awayTeam']['tla'] ?? 'TBD',
                'scheduled_at'   => $match['utcDate'],
                'stage'          => $match['stage'],
                'match_group'    => $match['group'] ?? null,
            ];

            $existing = Fixture::where('external_id', $match['id'])->first();

            if ($existing) {
                $existing->update($meta); // status/scores bewust niet bijwerken
            } else {
                Fixture::create($meta + ['external_id' => $match['id'], 'status' => 'SCHEDULED']);
            }
            $count++;
        }

        return $count;
    }

    /**
     * Live-sync: werkt status en (tussen)stand bij van de wedstrijden rond nu.
     * IN_PLAY/PAUSED → tussenstand, FINISHED → eindstand. Eenmaal afgeronde
     * wedstrijden worden niet meer aangeraakt (handmatige correcties blijven staan).
     * Geeft ['updated' => int, 'finished' => fixture-ids] terug.
     */
    public function syncLiveScores(): array
    {
        $apiKey = config('services.football_api.key', '');
        $competitionId = config('services.football_api.competition_id', 'WC');

        $response = Http::withHeaders(['X-Auth-Token' => $apiKey])
            ->get("{$this->baseUrl}/competitions/{$competitionId}/matches", [
                'dateFrom' => now('UTC')->subDay()->toDateString(),
                'dateTo'   => now('UTC')->addDay()->toDateString(),
            ]);

        if (! $response->ok()) {
            throw new \Exception("Football API fout: {$response->status()} {$response->reason()}");
        }

        $updated = 0;
        $finished = [];

        foreach ($response->json('matches', []) as $match) {
            $fixture = Fixture::where('external_id', $match['id'])->first();
            if (! $fixture || $fixture->isFinished()) {
                continue;
            }

            $home = $match['score']['fullTime']['home'] ?? null;
            $away = $match['score']['fullTime']['away'] ?? null;

            if (in_array($match['status'], ['IN_PLAY', 'PAUSED'])) {
                $fixture->update([
                    'status'     => 'IN_PLAY',
                    'home_score' => $home ?? 0,
                    'away_score' => $away ?? 0,
                ]);
                $updated++;
            } elseif ($match['status'] === 'FINISHED' && $home !== null && $away !== null) {
                $fixture->update([
                    'status'            => 'FINISHED',
                    'home_score'        => $home,
                    'away_score'        => $away,
                    'first_goal_minute' => $fixture->first_goal_minute ?? $this->fetchFirstGoalMinute($match['id']),
                ]);
                $finished[] = $fixture->id;
                $updated++;
            } elseif (in_array($match['status'], ['SCHEDULED', 'TIMED']) && $fixture->status === 'IN_PLAY') {
                // Handmatig (of per ongeluk) op IN_PLAY gezet terwijl de wedstrijd
                // nog niet bezig is: terugzetten, anders blijft hij eeuwig "live".
                $fixture->update(['status' => 'SCHEDULED', 'home_score' => null, 'away_score' => null]);
                $updated++;
            }
        }

        return ['updated' => $updated, 'finished' => $finished];
    }

    /** Minuut van het eerste doelpunt, via het wedstrijddetail (goals staan niet in de lijst-response). */
    private function fetchFirstGoalMinute(int $externalId): ?int
    {
        try {
            $response = Http::withHeaders(['X-Auth-Token' => config('services.football_api.key', '')])
                ->get("{$this->baseUrl}/matches/{$externalId}");

            if (! $response->ok()) {
                return null;
            }

            return collect($response->json('goals', []))
                ->pluck('minute')
                ->filter(fn ($m) => is_numeric($m))
                ->min();
        } catch (\Throwable) {
            return null;
        }
    }
}
