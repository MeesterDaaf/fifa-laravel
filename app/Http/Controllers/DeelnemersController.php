<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\TournamentPrediction;
use App\Models\TournamentResult;
use App\Models\User;
use App\Services\ScoringService;

class DeelnemersController extends Controller
{
    public function __construct(private ScoringService $scoring) {}

    public function index()
    {
        // Gesorteerde deelnemerslijst met punten (alleen niet-admins).
        $participants = $this->scoring->getLeaderboard();

        return view('deelnemers.index', compact('participants'));
    }

    public function show(User $user)
    {
        $fixtures = Fixture::orderBy('scheduled_at')->get();

        $predictions = Prediction::where('user_id', $user->id)
            ->get()
            ->keyBy('fixture_id');

        $byStage = $fixtures->groupBy('stage');

        $tournament = TournamentPrediction::where('user_id', $user->id)->first();
        $tournamentResult = TournamentResult::find('singleton');

        return view('deelnemers.show', compact(
            'user', 'byStage', 'predictions', 'tournament', 'tournamentResult'
        ));
    }
}
