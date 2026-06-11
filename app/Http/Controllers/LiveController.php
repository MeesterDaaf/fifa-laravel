<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Services\ScoringService;

class LiveController extends Controller
{
    public function __construct(private ScoringService $scoring) {}

    public function index()
    {
        $liveFixtures = Fixture::with(['predictions.user:id,name,is_admin,is_bot'])
            ->live()
            ->orderBy('scheduled_at')
            ->get();

        // Per live wedstrijd: alle voorspellingen + virtuele punten bij de huidige tussenstand.
        $virtualByUser = [];
        $liveBlocks = $liveFixtures->map(function ($fixture) use (&$virtualByUser) {
            $points = $this->scoring->virtualMatchPoints($fixture);

            foreach ($points as $userId => $pts) {
                $virtualByUser[$userId] = ($virtualByUser[$userId] ?? 0) + $pts;
            }

            $predictions = $fixture->predictions
                ->filter(fn ($p) => ! $p->user->is_admin)
                ->sortBy([
                    fn ($a, $b) => ($points[$b->user_id] ?? 0) <=> ($points[$a->user_id] ?? 0),
                    fn ($a, $b) => strcasecmp($a->user->name, $b->user->name),
                ])
                ->values();

            return ['fixture' => $fixture, 'predictions' => $predictions, 'points' => $points];
        });

        // Virtuele stand: huidige ranglijst + virtuele punten van de lopende wedstrijden.
        $virtualStandings = collect($this->scoring->getLeaderboard())
            ->map(function ($entry) use ($virtualByUser) {
                $entry['virtualGain'] = $virtualByUser[$entry['id']] ?? 0;
                $entry['virtualTotal'] = $entry['totalPoints'] + $entry['virtualGain'];
                return $entry;
            })
            ->sortByDesc('virtualTotal')
            ->values()
            ->map(function ($entry, $i) {
                $entry['virtualRank'] = $i + 1;
                $entry['delta'] = $entry['rank'] - $entry['virtualRank']; // + = stijgt in de virtuele stand
                return $entry;
            });

        $nextFixture = $liveFixtures->isEmpty()
            ? Fixture::where('status', 'SCHEDULED')
                ->where('scheduled_at', '>', now())
                ->orderBy('scheduled_at')
                ->first()
            : null;

        return view('live.index', compact('liveBlocks', 'virtualStandings', 'nextFixture'));
    }
}
