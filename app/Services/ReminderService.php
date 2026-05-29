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
        foreach (User::all() as $user) {
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
