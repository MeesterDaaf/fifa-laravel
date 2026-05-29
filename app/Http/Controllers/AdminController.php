<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Setting;
use App\Models\TournamentResult;
use App\Models\User;
use App\Services\FootballApiService;
use App\Services\ScoringService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        private ScoringService $scoring,
        private FootballApiService $footballApi,
    ) {}

    public function index()
    {
        $fixtures = Fixture::withCount('predictions')->orderBy('scheduled_at')->get();
        $tournamentResult = TournamentResult::find('singleton');
        $totalUsers = User::where('is_admin', false)->count();
        $inviteCode = Setting::inviteCode();
        $baseUrl = config('app.url');
        $teams = \App\Models\Team::orderBy('name')->get();

        return view('admin.index', compact('fixtures', 'tournamentResult', 'totalUsers', 'inviteCode', 'baseUrl', 'teams'));
    }

    public function syncMatches()
    {
        try {
            $count = $this->footballApi->syncMatches();
            return back()->with('success', "{$count} wedstrijden gesynchroniseerd! ✅");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function syncSquads()
    {
        try {
            $r = $this->footballApi->syncTeamsAndSquads();
            return back()->with('success', "{$r['teams']} teams en {$r['players']} spelers gesynchroniseerd! ✅");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateMatch(Request $request, int $id)
    {
        $fixture = Fixture::findOrFail($id);

        $data = $request->validate([
            'status'            => 'required|in:SCHEDULED,IN_PLAY,FINISHED',
            'home_score'        => 'nullable|integer|min:0',
            'away_score'        => 'nullable|integer|min:0',
            'first_goal_minute' => 'nullable|integer|min:1|max:120',
        ]);

        $fixture->update($data);

        if ($data['status'] === 'FINISHED' && $data['home_score'] !== null) {
            $this->scoring->calculateMatchPoints($fixture->id);
        }

        return back()->with('success', 'Wedstrijd bijgewerkt! ✅');
    }

    public function updateTournament(Request $request)
    {
        $data = $request->validate([
            'top_scorer'         => 'nullable|string|max:100',
            'champion'           => 'nullable|string|max:100',
            'total_yellow_cards' => 'nullable|integer|min:0|max:2000',
            'total_red_cards'    => 'nullable|integer|min:0|max:500',
        ]);

        $result = TournamentResult::updateOrCreate(
            ['id' => 'singleton'],
            [
                'top_scorer'         => $data['top_scorer'] ?? null,
                'champion'           => $data['champion'] ?? null,
                'total_yellow_cards' => $data['total_yellow_cards'] ?? null,
                'total_red_cards'    => $data['total_red_cards'] ?? null,
            ]
        );

        $this->scoring->calculateTournamentPoints($result);

        return back()->with('success', 'Toernooi resultaat bijgewerkt & punten herberekend! ✅');
    }

    public function regenerateInviteCode()
    {
        Setting::regenerateInviteCode();
        return back()->with('success', 'Uitnodigingscode vernieuwd! ✅');
    }
}
