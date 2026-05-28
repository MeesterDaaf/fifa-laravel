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

        return view('voorspellingen.index', compact('byStage', 'myPredictions'));
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

        return view('voorspellingen.show', compact('fixture', 'myPrediction', 'allPredictions', 'probability'));
    }

    public function store(Request $request, int $id)
    {
        $fixture = Fixture::findOrFail($id);

        if (! $fixture->isOpen()) {
            return back()->with('error', 'Voorspelling is gesloten.');
        }

        $data = $request->validate([
            'home_score'               => 'required|integer|min:0|max:30',
            'away_score'               => 'required|integer|min:0|max:30',
            'first_goal_minute'        => 'nullable|integer|min:1|max:120',
            'first_yellow_card_minute' => 'nullable|integer|min:1|max:120',
        ]);

        Prediction::updateOrCreate(
            ['user_id' => auth()->id(), 'fixture_id' => $id],
            $data
        );

        return redirect("/voorspellingen/{$id}")->with('success', 'Voorspelling opgeslagen! ✅');
    }
}
