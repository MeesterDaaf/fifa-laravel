<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\TournamentPrediction;
use App\Models\TournamentResult;
use App\Models\User;

class DeelnemersController extends Controller
{
    public function show(User $user)
    {
        $fixtures = Fixture::orderBy('scheduled_at')->get();

        $predictions = Prediction::where('user_id', $user->id)
            ->get()
            ->keyBy('fixture_id');

        $byStage = $fixtures->groupBy('stage');

        $tournament = TournamentPrediction::where('user_id', $user->id)->first();
        $tournamentResult = TournamentResult::find('singleton');

        // Voorspellingen van anderen blijven verborgen zolang er nog voorspeld
        // kan worden (eigen voorspellingen zijn altijd zichtbaar).
        $isOwner = $user->id === auth()->id();
        $tournamentHidden = ! $isOwner && TournamentPrediction::isOpen();

        return view('deelnemers.show', compact(
            'user', 'byStage', 'predictions', 'tournament', 'tournamentResult',
            'isOwner', 'tournamentHidden'
        ));
    }
}
