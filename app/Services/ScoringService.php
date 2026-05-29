<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\TournamentPrediction;
use App\Models\TournamentResult;
use App\Models\User;

class ScoringService
{
    public function calculateMatchPoints(int $fixtureId): void
    {
        $fixture = Fixture::with('predictions')->findOrFail($fixtureId);

        if ($fixture->home_score === null || $fixture->away_score === null) {
            return;
        }

        $actualResult = $this->getResult($fixture->home_score, $fixture->away_score);

        $scored = $fixture->predictions->map(function ($pred) use ($fixture, $actualResult) {
            $pointsScore = 0;
            if ($pred->home_score === $fixture->home_score && $pred->away_score === $fixture->away_score) {
                $pointsScore = 5;
            } elseif ($this->getResult($pred->home_score, $pred->away_score) === $actualResult) {
                $pointsScore = 2;
            }
            return ['pred' => $pred, 'pointsScore' => $pointsScore];
        });

        $goalBonusId = $this->findBonusWinner(
            $scored,
            $fixture->first_goal_minute,
            fn($p) => $p['pred']->first_goal_minute,
        );

        foreach ($scored as $item) {
            $pred = $item['pred'];
            $pointsGoal = $pred->id === $goalBonusId ? 3 : 0;

            $pred->update([
                'points_score'        => $item['pointsScore'],
                'points_goal_minute'  => $pointsGoal,
                'total_points'        => $item['pointsScore'] + $pointsGoal,
                'calculated_at'       => now(),
            ]);
        }
    }

    /**
     * Berekent toernooipunten op basis van de officiële uitslag.
     *  - Topscorer juist (exacte naam):           10 punten
     *  - Toernooiwinnaar juist (exact land):       15 punten
     *  - Dichtst bij totaal gele kaarten:           5 punten (1 winnaar)
     *  - Dichtst bij totaal rode kaarten:           5 punten (1 winnaar)
     */
    public function calculateTournamentPoints(TournamentResult $result): void
    {
        $preds = TournamentPrediction::all();

        $yellowWinnerId = $this->closestPredictionId($preds, $result->total_yellow_cards, fn($p) => $p->total_yellow_cards);
        $redWinnerId    = $this->closestPredictionId($preds, $result->total_red_cards, fn($p) => $p->total_red_cards);

        foreach ($preds as $pred) {
            $pTop = $this->namesMatch($pred->top_scorer, $result->top_scorer) ? 10 : 0;
            $pChampion = $this->namesMatch($pred->champion, $result->champion) ? 15 : 0;
            $pYellow = $pred->id === $yellowWinnerId ? 5 : 0;
            $pRed = $pred->id === $redWinnerId ? 5 : 0;

            $pred->update([
                'points_top_scorer' => $pTop,
                'points_champion'   => $pChampion,
                'points_yellow'     => $pYellow,
                'points_red'        => $pRed,
                'points'            => $pTop + $pChampion + $pYellow + $pRed,
            ]);
        }
    }

    private function namesMatch(?string $a, ?string $b): bool
    {
        if ($a === null || $b === null || trim($a) === '' || trim($b) === '') {
            return false;
        }
        return strtolower(trim($a)) === strtolower(trim($b));
    }

    /** Geeft het id van de voorspelling die het dichtst bij $actual zit (1 unieke winnaar). */
    private function closestPredictionId(\Illuminate\Support\Collection $preds, ?int $actual, callable $getter): ?int
    {
        if ($actual === null) {
            return null;
        }

        $valid = $preds->filter(fn($p) => $getter($p) !== null);
        if ($valid->isEmpty()) {
            return null;
        }

        $minDiff = $valid->min(fn($p) => abs($getter($p) - $actual));
        $winners = $valid->filter(fn($p) => abs($getter($p) - $actual) === $minDiff);

        return $winners->count() === 1 ? $winners->first()->id : null;
    }

    public function getLeaderboard(): array
    {
        return User::where('is_admin', false)
            ->with(['predictions:user_id,total_points', 'tournamentPrediction:user_id,points'])
            ->get(['id', 'name', 'is_bot'])
            ->map(function ($user) {
                $matchPoints = $user->predictions->sum('total_points');
                $tournamentPoints = $user->tournamentPrediction?->points ?? 0;
                return [
                    'id'               => $user->id,
                    'name'             => $user->name,
                    'is_bot'           => $user->is_bot,
                    'matchPoints'      => $matchPoints,
                    'tournamentPoints' => $tournamentPoints,
                    'totalPoints'      => $matchPoints + $tournamentPoints,
                    'predictionsCount' => $user->predictions->count(),
                ];
            })
            ->sortByDesc('totalPoints')
            ->values()
            ->toArray();
    }

    private function findBonusWinner(\Illuminate\Support\Collection $scored, ?int $actual, callable $getter): ?int
    {
        if ($actual === null) {
            return null;
        }

        $valid = $scored->filter(fn($p) => $getter($p) !== null);
        if ($valid->isEmpty()) {
            return null;
        }

        $minDiff = $valid->min(fn($p) => abs($getter($p) - $actual));
        $winners = $valid->filter(fn($p) => abs($getter($p) - $actual) === $minDiff);

        return $winners->count() === 1 ? $winners->first()['pred']->id : null;
    }

    private function getResult(int $home, int $away): string
    {
        if ($home > $away) return 'HOME';
        if ($home < $away) return 'AWAY';
        return 'DRAW';
    }
}
