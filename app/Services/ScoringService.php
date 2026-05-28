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

        $yellowBonusId = $this->findBonusWinner(
            $scored,
            $fixture->first_yellow_card_minute,
            fn($p) => $p['pred']->first_yellow_card_minute,
        );

        $goalBonusId = $this->findBonusWinner(
            $scored,
            $fixture->first_goal_minute,
            fn($p) => $p['pred']->first_goal_minute,
        );

        foreach ($scored as $item) {
            $pred = $item['pred'];
            $pointsYellow = $pred->id === $yellowBonusId ? 3 : 0;
            $pointsGoal = $pred->id === $goalBonusId ? 3 : 0;

            $pred->update([
                'points_score'        => $item['pointsScore'],
                'points_yellow'       => $pointsYellow,
                'points_goal_minute'  => $pointsGoal,
                'total_points'        => $item['pointsScore'] + $pointsYellow + $pointsGoal,
                'calculated_at'       => now(),
            ]);
        }
    }

    public function calculateTournamentPoints(string $actualTopScorer): void
    {
        TournamentPrediction::all()->each(function ($pred) use ($actualTopScorer) {
            $points = strtolower(trim($pred->top_scorer)) === strtolower(trim($actualTopScorer)) ? 10 : 0;
            $pred->update(['points' => $points]);
        });
    }

    public function getLeaderboard(): array
    {
        return User::where('is_admin', false)
            ->with(['predictions:user_id,total_points', 'tournamentPrediction:user_id,points'])
            ->get(['id', 'name'])
            ->map(function ($user) {
                $matchPoints = $user->predictions->sum('total_points');
                $tournamentPoints = $user->tournamentPrediction?->points ?? 0;
                return [
                    'id'               => $user->id,
                    'name'             => $user->name,
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
