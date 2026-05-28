<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TournamentPrediction;
use App\Models\TournamentResult;
use Illuminate\Http\Request;

class ToernooiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $myPrediction = TournamentPrediction::where('user_id', $user->id)->first();
        $tournamentResult = TournamentResult::find('singleton');
        $allPredictions = TournamentPrediction::with('user:id,name')
            ->orderBy('top_scorer')
            ->get();

        // Landen voor de picker (alleen die met spelers).
        $teams = Team::has('players')->orderBy('name')->get();

        // Geselecteerd land + squad (gesorteerd: aanvallers eerst).
        $selectedTeam = null;
        $squad = collect();
        if ($request->filled('team')) {
            $selectedTeam = Team::where('tla', $request->query('team'))
                ->orWhere('id', $request->query('team'))
                ->first();

            if ($selectedTeam) {
                $squad = $selectedTeam->players
                    ->sortBy([
                        fn ($a, $b) => $a->positionRank() <=> $b->positionRank(),
                        fn ($a, $b) => strcmp($a->name, $b->name),
                    ])
                    ->groupBy(fn ($p) => $p->positionGroup());
            }
        }

        return view('toernooi.index', compact(
            'myPrediction', 'tournamentResult', 'allPredictions',
            'teams', 'selectedTeam', 'squad'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'top_scorer' => 'required|string|max:100',
        ]);

        TournamentPrediction::updateOrCreate(
            ['user_id' => auth()->id()],
            $data
        );

        return redirect('/toernooi')->with('success', 'Topscorer-voorspelling opgeslagen! ✅');
    }
}
