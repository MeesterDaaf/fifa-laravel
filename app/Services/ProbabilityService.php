<?php

namespace App\Services;

use App\Models\Fixture;

class ProbabilityService
{
    /**
     * Bereken win/gelijk/verlies-kansen voor een wedstrijd op basis van Elo.
     *
     * Methode:
     *   1. Win-verwachting van het thuisteam:  We = 1 / (1 + 10^(-dr/400))
     *      waarbij dr = Elo_thuis + thuisvoordeel - Elo_uit
     *   2. Gelijkspelkans volgt een Gauss-curve die piekt bij gelijke teams.
     *   3. Win/verlies = win-verwachting minus de helft van de gelijkspelkans,
     *      zodat de verwachte score consistent blijft (pWin + pDraw/2 = We).
     *
     * @return array{home: float, draw: float, away: float, known: bool}
     */
    public function forFixture(Fixture $fixture): array
    {
        $homeElo = $this->ratingFor($fixture->home_team_code);
        $awayElo = $this->ratingFor($fixture->away_team_code);

        // Geen betrouwbare kans als een team nog onbekend is (TBD).
        $known = $this->hasRating($fixture->home_team_code)
            && $this->hasRating($fixture->away_team_code);

        $dr = $homeElo + config('elo.home_advantage') - $awayElo;

        $winExpectancy = 1 / (1 + pow(10, -$dr / 400));

        $drawBase  = config('elo.draw_base');
        $drawSigma = config('elo.draw_sigma');
        $pDraw = $drawBase * exp(-($dr * $dr) / (2 * $drawSigma * $drawSigma));

        $pHome = $winExpectancy - $pDraw / 2;
        $pAway = (1 - $winExpectancy) - $pDraw / 2;

        // Klem op 0 en normaliseer zodat de som exact 1 is.
        $pHome = max(0, $pHome);
        $pAway = max(0, $pAway);
        $total = $pHome + $pAway + $pDraw;

        return [
            'home'  => round($pHome / $total * 100, 1),
            'draw'  => round($pDraw / $total * 100, 1),
            'away'  => round($pAway / $total * 100, 1),
            'known' => $known,
        ];
    }

    private function ratingFor(?string $code): int
    {
        return config("elo.ratings.{$code}", config('elo.default'));
    }

    private function hasRating(?string $code): bool
    {
        return $code !== null && array_key_exists($code, config('elo.ratings'));
    }
}
