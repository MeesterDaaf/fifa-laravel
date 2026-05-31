<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Services\ProbabilityService;
use Illuminate\Http\Request;

class VoorspellingenController extends Controller
{
    public function __construct(private ProbabilityService $probability) {}

    public function index()
    {
        $user = auth()->user();

        $fixtures = Fixture::orderBy('scheduled_at')->get();

        $myPredictions = Prediction::where('user_id', $user->id)
            ->get()
            ->keyBy('fixture_id');

        $byStage = $fixtures->groupBy('stage');

        // Aantal nog-open wedstrijden mét bekende teams die de gebruiker nog niet voorspeld heeft.
        $openUnpredicted = Fixture::openWithTeams()
            ->whereNotIn('id', $myPredictions->keys())
            ->count();

        return view('voorspellingen.index', compact('byStage', 'myPredictions', 'openUnpredicted'));
    }

    public function autoFill(\App\Services\AiPredictionService $ai)
    {
        $count = $ai->fillSuggestionsForUser(auth()->user());

        return redirect('/voorspellingen')->with(
            $count > 0 ? 'success' : 'error',
            $count > 0
                ? "{$count} wedstrijd(en) automatisch ingevuld op basis van de kansberekening. Je kunt ze nog aanpassen! ⚡"
                : 'Geen open wedstrijden om in te vullen (of je hebt ze al voorspeld).'
        );
    }

    public function show(int $id)
    {
        $user = auth()->user();
        $fixture = Fixture::findOrFail($id);

        $myPrediction = Prediction::where('user_id', $user->id)
            ->where('fixture_id', $id)
            ->first();

        $allPredictions = $fixture->isFinished()
            ? Prediction::with('user:id,name')
                ->where('fixture_id', $id)
                ->orderByDesc('total_points')
                ->get()
            : collect();

        $probability = $this->probability->forFixture($fixture);

        // Eerstvolgende nog-open wedstrijd die deze gebruiker nog niet voorspeld heeft.
        $predictedIds = Prediction::where('user_id', $user->id)->pluck('fixture_id');
        $nextFixture = Fixture::openForPredictions()
            ->where('id', '!=', $fixture->id)
            ->whereNotIn('id', $predictedIds)
            ->orderBy('scheduled_at')
            ->first();

        return view('voorspellingen.show', compact('fixture', 'myPrediction', 'allPredictions', 'probability', 'nextFixture'));
    }

    public function store(Request $request, int $id)
    {
        $fixture = Fixture::findOrFail($id);

        if (! $fixture->isOpen()) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Voorspelling is gesloten.'], 422);
            }
            return back()->with('error', 'Voorspelling is gesloten.');
        }

        // Bij autosave (AJAX) mogen lege scores nog → dan slaan we niet op, geen fout.
        $data = $request->validate([
            'home_score'        => 'required|integer|min:0|max:30',
            'away_score'        => 'required|integer|min:0|max:30',
            'first_goal_minute' => 'nullable|integer|min:1|max:120',
        ]);

        Prediction::updateOrCreate(
            ['user_id' => auth()->id(), 'fixture_id' => $id],
            $data
        );

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Automatisch opgeslagen']);
        }

        return redirect("/voorspellingen/{$id}")->with('success', 'Voorspelling opgeslagen! ✅');
    }
}
