<?php

namespace App\Services;

use App\Mail\MatchReminderMail;
use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class ReminderService
{
    /**
     * Bouwt een kant-en-klare herinneringstekst voor de eerstvolgende speeldag,
     * bedoeld om handmatig in een WhatsApp-groep te plakken. Null als er geen
     * open wedstrijden meer zijn.
     */
    public function whatsappText(): ?string
    {
        $next = Fixture::openForPredictions()->orderBy('scheduled_at')->first();
        if (! $next) {
            return null;
        }

        $date = $next->scheduled_at->copy();
        $matches = Fixture::whereDate('scheduled_at', $date->toDateString())
            ->where('status', 'SCHEDULED')
            ->orderBy('scheduled_at')
            ->get();

        $list = $matches
            ->map(fn ($m) => country_name($m->home_team_code, $m->home_team).'–'.country_name($m->away_team_code, $m->away_team))
            ->implode(', ');

        // Aantal echte deelnemers met nog minstens één onvoorspelde wedstrijd.
        $matchIds = $matches->pluck('id');
        $todo = User::where('is_bot', false)->where('is_admin', false)->get()
            ->filter(fn ($u) => Prediction::where('user_id', $u->id)->whereIn('fixture_id', $matchIds)->count() < $matchIds->count())
            ->count();

        return '⚽ Vergeet je voorspellingen niet! '.format_day($date).': '.$list.'. '
            .($todo > 0 ? "Nog {$todo} deelnemer(s) moeten invullen. " : '')
            .'👉 '.config('app.url');
    }

    /**
     * Stuurt herinneringen naar alle gebruikers die wedstrijden op $date
     * nog niet hebben voorspeld. Geeft het aantal verstuurde mails terug.
     */
    public function sendForDate(Carbon $date): int
    {
        $matches = $this->matchesOn($date);
        if ($matches->isEmpty()) {
            return 0;
        }

        $sent = 0;
        foreach (User::where('is_bot', false)->get() as $user) {
            if ($this->sendToUser($user, $matches, $date)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Stuurt één gebruiker een herinnering voor de wedstrijden op $date,
     * mits er nog onvoorspelde wedstrijden zijn. Geeft terug of er gemaild is.
     */
    public function sendForUser(User $user, Carbon $date): bool
    {
        $matches = $this->matchesOn($date);
        if ($matches->isEmpty()) {
            return false;
        }

        return $this->sendToUser($user, $matches, $date);
    }

    private function sendToUser(User $user, $matches, Carbon $date): bool
    {
        $predicted = Prediction::where('user_id', $user->id)
            ->whereIn('fixture_id', $matches->pluck('id'))
            ->pluck('fixture_id');

        $todo = $matches->whereNotIn('id', $predicted)->values();
        if ($todo->isEmpty()) {
            return false;
        }

        Mail::to($user->email)->send(new MatchReminderMail($user, $todo, $date));

        return true;
    }

    private function matchesOn(Carbon $date)
    {
        return Fixture::whereDate('scheduled_at', $date->toDateString())
            ->where('status', 'SCHEDULED')
            ->orderBy('scheduled_at')
            ->get();
    }
}
