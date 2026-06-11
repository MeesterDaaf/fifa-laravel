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
     *
     * Gebruikt bewust het detail-endpoint per wedstrijd: de competitielijst van
     * football-data.org loopt (door caching aan hun kant) soms flink achter,
     * het wedstrijddetail is actueel en bevat ook de doelpuntminuten.
     *
     * Geeft ['updated' => int, 'finished' => fixture-ids] terug.
     */
    public function syncLiveScores(): array
    {
        $apiKey = config('services.football_api.key', '');

        // Wedstrijden die nu bezig (horen te) zijn: IN_PLAY, of aftrap (bijna)
        // gepasseerd en nog niet afgerond. Max een handvol tegelijk, dus de
        // losse detail-calls blijven ruim binnen de rate limit (10/min).
        $candidates = Fixture::whereNotNull('external_id')
            ->where('status', '!=', 'FINISHED')
            ->where(fn ($q) => $q->where('status', 'IN_PLAY')
                ->orWhere(fn ($q2) => $q2->where('scheduled_at', '<=', now()->addMinutes(5))
                    ->where('scheduled_at', '>', now()->subHours(4))))
            ->get();

        $updated = 0;
        $finished = [];

        foreach ($candidates as $fixture) {
            $response = Http::withHeaders(['X-Auth-Token' => $apiKey])
                ->get("{$this->baseUrl}/matches/{$fixture->external_id}");

            if (! $response->ok()) {
                continue; // volgende run opnieuw proberen
            }

            $status = $response->json('status');
            $home = $response->json('score.fullTime.home');
            $away = $response->json('score.fullTime.away');

            $firstGoal = $fixture->first_goal_minute ?? collect($response->json('goals', []))
                ->pluck('minute')
                ->filter(fn ($m) => is_numeric($m))
                ->min();

            if (in_array($status, ['IN_PLAY', 'PAUSED', 'EXTRA_TIME', 'PENALTY_SHOOTOUT'])) {
                $fixture->update([
                    'status'            => 'IN_PLAY',
                    'home_score'        => $home ?? 0,
                    'away_score'        => $away ?? 0,
                    'first_goal_minute' => $firstGoal,
                ]);
                $updated++;
            } elseif ($status === 'FINISHED' && $home !== null && $away !== null) {
                $fixture->update([
                    'status'            => 'FINISHED',
                    'home_score'        => $home,
                    'away_score'        => $away,
                    'first_goal_minute' => $firstGoal,
                ]);
                $finished[] = $fixture->id;
                $updated++;
            } elseif (in_array($status, ['SCHEDULED', 'TIMED']) && $fixture->status === 'IN_PLAY' && $fixture->scheduled_at->isFuture()) {
                // Per ongeluk op IN_PLAY gezet terwijl de aftrap nog moet komen:
                // terugzetten. Ná de aftrap laten we een handmatige IN_PLAY juist
                // staan — dan loopt de API achter en wint de admin.
                $fixture->update(['status' => 'SCHEDULED', 'home_score' => null, 'away_score' => null]);
                $updated++;
            }
        }

        return ['updated' => $updated, 'finished' => $finished];
    }
}
