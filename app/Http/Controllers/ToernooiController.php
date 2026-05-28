<?php

namespace App\Http\Controllers;

use App\Models\TournamentPrediction;
use App\Models\TournamentResult;
use Illuminate\Http\Request;

class ToernooiController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $myPrediction = TournamentPrediction::where('user_id', $user->id)->first();
        $tournamentResult = TournamentResult::find('singleton');
        $allPredictions = TournamentPrediction::with('user:id,name')
            ->orderBy('top_scorer')
            ->get();

        return view('toernooi.index', compact('myPrediction', 'tournamentResult', 'allPredictions'));
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

        return redirect('/toernooi')->with('success', 'Toernooi voorspelling opgeslagen! ✅');
    }
}
