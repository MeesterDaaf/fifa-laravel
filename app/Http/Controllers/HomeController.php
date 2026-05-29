<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\TournamentPrediction;
use App\Services\ScoringService;

class HomeController extends Controller
{
    public function __construct(private ScoringService $scoring) {}

    public function index()
    {
        $user = auth()->user();
        $now = now();

        $upcoming = Fixture::where('status', 'SCHEDULED')
            ->where('scheduled_at', '>=', $now)
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        // Voortgang wedstrijd-voorspellingen (alleen wedstrijden die nog te
        // voorspellen zijn — gesloten 15 min vóór aftrap — tellen mee).
        $openFixtureIds = Fixture::openForPredictions()->pluck('id');
        $openCount = $openFixtureIds->count();
        $predictedCount = Prediction::where('user_id', $user->id)
            ->whereIn('fixture_id', $openFixtureIds)
            ->count();

        // Voortgang toernooi-voorspellingen (4 onderdelen).
        $tournament = TournamentPrediction::where('user_id', $user->id)->first();
        $tournamentStatus = [
            'champion'   => filled($tournament?->champion),
            'top_scorer' => filled($tournament?->top_scorer),
            'yellow'     => $tournament?->total_yellow_cards !== null,
            'red'        => $tournament?->total_red_cards !== null,
        ];
        $tournamentDone = count(array_filter($tournamentStatus));

        $recent = Fixture::where('status', 'FINISHED')
            ->orderByDesc('scheduled_at')
            ->limit(5)
            ->get();

        $myPredIds = Prediction::where('user_id', $user->id)
            ->whereIn('fixture_id', $upcoming->pluck('id'))
            ->pluck('fixture_id')
            ->flip();

        $leaderboard = array_slice($this->scoring->getLeaderboard(), 0, 5);

        return view('home.index', compact(
            'upcoming', 'recent', 'myPredIds', 'leaderboard',
            'openCount', 'predictedCount', 'tournamentStatus', 'tournamentDone'
        ));
    }
}
