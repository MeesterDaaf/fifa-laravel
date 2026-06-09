<?php

namespace App\Services;

use App\Models\TriviaQuestion;
use App\Models\TriviaVote;
use Illuminate\Support\Facades\DB;

class DailyTriviaService
{
    /**
     * De vraag van vandaag. Wijst (race-veilig) de volgende ongebruikte vraag
     * aan vandaag toe als dat nog niet is gebeurd. Geeft null als alle vragen op zijn.
     */
    public function todaysQuestion(): ?TriviaQuestion
    {
        $today = today();

        if ($existing = TriviaQuestion::whereDate('used_on', $today)->first()) {
            return $existing;
        }

        return DB::transaction(function () use ($today) {
            // Dubbelcheck binnen de transactie (twee gelijktijdige eerste bezoekers).
            if ($existing = TriviaQuestion::whereDate('used_on', $today)->lockForUpdate()->first()) {
                return $existing;
            }

            $next = TriviaQuestion::whereNull('used_on')
                ->inRandomOrder()
                ->lockForUpdate()
                ->first();

            if (! $next) {
                return null; // Alle vragen zijn al een keer geweest.
            }

            $next->update(['used_on' => $today]);

            return $next;
        });
    }

    /**
     * Stemverdeling per optie: aantal + percentage. Vorm:
     * ['total' => int, 'options' => [index => ['count' => int, 'pct' => int]]]
     */
    public function results(TriviaQuestion $question): array
    {
        $counts = TriviaVote::where('trivia_question_id', $question->id)
            ->selectRaw('choice, COUNT(*) as c')
            ->groupBy('choice')
            ->pluck('c', 'choice');

        $total = (int) $counts->sum();

        $options = [];
        foreach ($question->options as $i => $_label) {
            $n = (int) ($counts[$i] ?? 0);
            $options[$i] = [
                'count' => $n,
                'pct'   => $total > 0 ? (int) round($n / $total * 100) : 0,
            ];
        }

        return ['total' => $total, 'options' => $options];
    }
}
