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
        // Pas zichtbaar na de deadline, zodat niemand kan afkijken.
        $allPredictions = TournamentPrediction::isOpen()
            ? collect()
            : TournamentPrediction::with('user:id,name')->orderBy('top_scorer')->get();

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

        $isOpen = TournamentPrediction::isOpen();
        $deadline = TournamentPrediction::deadline();

        return view('toernooi.index', compact(
            'myPrediction', 'tournamentResult', 'allPredictions',
            'teams', 'selectedTeam', 'squad', 'isOpen', 'deadline'
        ));
    }

    public function store(Request $request)
    {
        // De toernooivoorspelling sluit zodra de eerste wedstrijd begint.
        if (! TournamentPrediction::isOpen()) {
            return redirect('/toernooi')
                ->with('error', 'De toernooivoorspelling is gesloten — het toernooi is begonnen.');
        }

        $request->validate([
            'top_scorer'         => 'nullable|string|max:100',
            'champion'           => 'nullable|string|max:100',
            'total_yellow_cards' => 'nullable|integer|min:0|max:2000',
            'total_red_cards'    => 'nullable|integer|min:0|max:500',
        ]);

        // Werk alleen de velden bij die in dit formulier zijn meegestuurd,
        // zodat de topscorer-picker en het kaarten/winnaar-formulier los werken.
        $fields = ['top_scorer', 'champion', 'total_yellow_cards', 'total_red_cards'];
        $update = [];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                $update[$field] = ($value === '' ? null : $value);
            }
        }

        TournamentPrediction::updateOrCreate(
            ['user_id' => auth()->id()],
            $update
        );

        return redirect('/toernooi')->with('success', 'Voorspelling opgeslagen! ✅');
    }
}
