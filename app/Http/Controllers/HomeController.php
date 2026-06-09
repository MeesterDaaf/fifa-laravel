<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\TournamentPrediction;
use App\Models\TriviaVote;
use App\Services\DailyTriviaService;
use App\Services\ScoringService;

class HomeController extends Controller
{
    public function __construct(private ScoringService $scoring) {}

    public function index(DailyTriviaService $dailyTrivia)
    {
        $user = auth()->user();
        $now = now();

        $upcoming = Fixture::where('status', 'SCHEDULED')
            ->where('scheduled_at', '>=', $now)
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        // Voortgang wedstrijd-voorspellingen: alleen open wedstrijden mét bekende
        // teams tellen mee (TBD/knock-out zonder loting niet).
        $openFixtureIds = Fixture::openWithTeams()->pluck('id');
        $openCount = $openFixtureIds->count();
        $predictedCount = Prediction::where('user_id', $user->id)
            ->whereIn('fixture_id', $openFixtureIds)
            ->count();

        // Voortgang toernooi-voorspellingen (4 onderdelen).
        $tournament = TournamentPrediction::where('user_id', $user->id)->first();
        $tournamentStatus = [
            'champion'   => filled($tournament?->champion),
            'top_scorer' => filled($tournament?->top_scorer),
            'yellow'     => $tournament?->total_yellow_cards !== null,
            'red'        => $tournament?->total_red_cards !== null,
        ];
        $tournamentDone = count(array_filter($tournamentStatus));

        $recent = Fixture::where('status', 'FINISHED')
            ->orderByDesc('scheduled_at')
            ->limit(5)
            ->get();

        $myPredIds = Prediction::where('user_id', $user->id)
            ->whereIn('fixture_id', $upcoming->pluck('id'))
            ->pluck('fixture_id')
            ->flip();

        $leaderboard = array_slice($this->scoring->getLeaderboard(), 0, 5);

        $whatsappGroupUrl = \App\Models\Setting::whatsappGroupUrl();

        // Voor admins: aantal gespeelde wedstrijden dat nog op invoer wacht.
        $awaitingResults = $user->is_admin ? Fixture::awaitingResult()->count() : 0;

        // Vraag van de dag (grappige trivia, telt niet mee).
        $trivia = $dailyTrivia->todaysQuestion();
        $triviaVote = $trivia
            ? TriviaVote::where('trivia_question_id', $trivia->id)->where('user_id', $user->id)->first()
            : null;
        $triviaResults = ($trivia && $triviaVote) ? $dailyTrivia->results($trivia) : null;

        return view('home.index', compact(
            'upcoming', 'recent', 'myPredIds', 'leaderboard',
            'openCount', 'predictedCount', 'tournamentStatus', 'tournamentDone',
            'whatsappGroupUrl', 'awaitingResults',
            'trivia', 'triviaVote', 'triviaResults'
        ));
    }
}
