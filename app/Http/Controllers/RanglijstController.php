<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Services\ScoringService;

class RanglijstController extends Controller
{
    public function __construct(private ScoringService $scoring) {}

    public function index()
    {
        $leaderboard = $this->scoring->getLeaderboard();
        $totalMatches = Fixture::where('status', 'FINISHED')->count();
        $myId = auth()->id();
        $myPos = false;

        foreach ($leaderboard as $i => $entry) {
            if ($entry['id'] === $myId) {
                $myPos = $i;
                break;
            }
        }

        return view('ranglijst.index', compact('leaderboard', 'totalMatches', 'myId', 'myPos'));
    }
}
