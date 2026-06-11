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
                $pointsScore = config('scoring.match.exact');
            } elseif ($this->getResult($pred->home_score, $pred->away_score) === $actualResult) {
                $pointsScore = config('scoring.match.outcome');
            }
            return ['pred' => $pred, 'pointsScore' => $pointsScore];
        });

        $goalBonusIds = $this->findClosestIds(
            $scored,
            $fixture->first_goal_minute,
            fn($p) => $p['pred']->first_goal_minute,
        );

        foreach ($scored as $item) {
            $pred = $item['pred'];
            $pointsGoal = $goalBonusIds->contains($pred->id) ? config('scoring.match.goal_minute_bonus') : 0;

            $pred->update([
                'points_score'        => $item['pointsScore'],
                'points_goal_minute'  => $pointsGoal,
                'total_points'        => $item['pointsScore'] + $pointsGoal,
                'calculated_at'       => now(),
            ]);
        }
    }

    /**
     * Virtuele punten per gebruiker (user_id => punten) alsof de huidige
     * (tussen)stand de eindstand is. Slaat niets op. Zonder ingevoerde
     * tussenstand wordt 0-0 aangehouden.
     */
    public function virtualMatchPoints(Fixture $fixture): \Illuminate\Support\Collection
    {
        $home = $fixture->home_score ?? 0;
        $away = $fixture->away_score ?? 0;
        $actualResult = $this->getResult($home, $away);

        $scored = $fixture->predictions->map(function ($pred) use ($home, $away, $actualResult) {
            $pointsScore = 0;
            if ($pred->home_score === $home && $pred->away_score === $away) {
                $pointsScore = config('scoring.match.exact');
            } elseif ($this->getResult($pred->home_score, $pred->away_score) === $actualResult) {
                $pointsScore = config('scoring.match.outcome');
            }
            return ['pred' => $pred, 'pointsScore' => $pointsScore];
        });

        $goalBonusIds = $this->findClosestIds(
            $scored,
            $fixture->first_goal_minute,
            fn ($p) => $p['pred']->first_goal_minute,
        );

        return $scored->mapWithKeys(function ($item) use ($goalBonusIds) {
            $bonus = $goalBonusIds->contains($item['pred']->id) ? config('scoring.match.goal_minute_bonus') : 0;
            return [$item['pred']->user_id => $item['pointsScore'] + $bonus];
        });
    }

    /**
     * Berekent toernooipunten op basis van de officiële uitslag.
     * Puntwaarden komen uit config/scoring.php (tournament.*).
     */
    public function calculateTournamentPoints(TournamentResult $result): void
    {
        $preds = TournamentPrediction::all();

        $yellowWinnerIds = $this->closestPredictionIds($preds, $result->total_yellow_cards, fn($p) => $p->total_yellow_cards);
        $redWinnerIds    = $this->closestPredictionIds($preds, $result->total_red_cards, fn($p) => $p->total_red_cards);

        foreach ($preds as $pred) {
            $pTop = $this->namesMatch($pred->top_scorer, $result->top_scorer) ? config('scoring.tournament.top_scorer') : 0;
            $pChampion = $this->namesMatch($pred->champion, $result->champion) ? config('scoring.tournament.champion') : 0;
            $pYellow = $yellowWinnerIds->contains($pred->id) ? config('scoring.tournament.yellow_cards') : 0;
            $pRed = $redWinnerIds->contains($pred->id) ? config('scoring.tournament.red_cards') : 0;

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

    /** Ids van álle toernooi-voorspellingen die het dichtst bij $actual zitten (gedeelde winnaars krijgen allemaal de bonus). */
    private function closestPredictionIds(\Illuminate\Support\Collection $preds, ?int $actual, callable $getter): \Illuminate\Support\Collection
    {
        if ($actual === null) {
            return collect();
        }

        $valid = $preds->filter(fn($p) => $getter($p) !== null);
        if ($valid->isEmpty()) {
            return collect();
        }

        $minDiff = $valid->min(fn($p) => abs($getter($p) - $actual));

        return $valid->filter(fn($p) => abs($getter($p) - $actual) === $minDiff)->pluck('id');
    }

    public function getLeaderboard(): array
    {
        return User::where('is_admin', false)
            ->with(['predictions:user_id,total_points', 'tournamentPrediction:user_id,points'])
            ->get(['id', 'name', 'is_bot', 'previous_rank'])
            ->map(function ($user) {
                $matchPoints = $user->predictions->sum('total_points');
                $tournamentPoints = $user->tournamentPrediction?->points ?? 0;
                return [
                    'id'               => $user->id,
                    'name'             => $user->name,
                    'is_bot'           => $user->is_bot,
                    'previous_rank'    => $user->previous_rank,
                    'matchPoints'      => $matchPoints,
                    'tournamentPoints' => $tournamentPoints,
                    'totalPoints'      => $matchPoints + $tournamentPoints,
                    'predictionsCount' => $user->predictions->count(),
                ];
            })
            ->sortByDesc('totalPoints')
            ->values()
            ->map(function ($entry, $i) {
                $entry['rank'] = $i + 1;
                // beweging t.o.v. het laatst vastgelegde ijkpunt (+ = gestegen)
                $entry['movement'] = $entry['previous_rank'] !== null
                    ? $entry['previous_rank'] - $entry['rank']
                    : null;
                return $entry;
            })
            ->toArray();
    }

    /** Legt de huidige ranglijst vast als ijkpunt (voor de ▲/▼ pijltjes). */
    public function captureRanking(): void
    {
        foreach ($this->getLeaderboard() as $entry) {
            User::whereKey($entry['id'])->update(['previous_rank' => $entry['rank']]);
        }

        \App\Models\Setting::updateOrCreate(['id' => 'singleton'], ['ranking_captured_at' => now()]);
    }

    /** Ids van álle wedstrijd-voorspellingen die het dichtst bij $actual zitten (gedeelde winnaars krijgen allemaal de bonus). */
    private function findClosestIds(\Illuminate\Support\Collection $scored, ?int $actual, callable $getter): \Illuminate\Support\Collection
    {
        if ($actual === null) {
            return collect();
        }

        $valid = $scored->filter(fn($p) => $getter($p) !== null);
        if ($valid->isEmpty()) {
            return collect();
        }

        $minDiff = $valid->min(fn($p) => abs($getter($p) - $actual));

        return $valid->filter(fn($p) => abs($getter($p) - $actual) === $minDiff)
            ->map(fn($p) => $p['pred']->id)
            ->values();
    }

    private function getResult(int $home, int $away): string
    {
        if ($home > $away) return 'HOME';
        if ($home < $away) return 'AWAY';
        return 'DRAW';
    }
}
