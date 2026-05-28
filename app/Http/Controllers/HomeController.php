<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Prediction;
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

        $recent = Fixture::where('status', 'FINISHED')
            ->orderByDesc('scheduled_at')
            ->limit(5)
            ->get();

        $myPredIds = Prediction::where('user_id', $user->id)
            ->whereIn('fixture_id', $upcoming->pluck('id'))
            ->pluck('fixture_id')
            ->flip();

        $leaderboard = array_slice($this->scoring->getLeaderboard(), 0, 5);

        return view('home.index', compact('upcoming', 'recent', 'myPredIds', 'leaderboard'));
    }
}
