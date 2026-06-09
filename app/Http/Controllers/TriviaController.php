<?php

namespace App\Http\Controllers;

use App\Models\TriviaQuestion;
use App\Models\TriviaVote;
use Illuminate\Http\Request;

class TriviaController extends Controller
{
    public function vote(Request $request, TriviaQuestion $question)
    {
        $data = $request->validate([
            'choice' => 'required|integer|min:0|max:' . (count($question->options) - 1),
        ]);

        // Alleen de vraag van vandaag mag beantwoord worden.
        if (! $question->used_on || ! $question->used_on->isToday()) {
            return back()->with('error', 'Deze vraag kan niet meer beantwoord worden.');
        }

        // Eén antwoord per deelnemer: bestaande stem blijft staan.
        TriviaVote::firstOrCreate(
            ['trivia_question_id' => $question->id, 'user_id' => auth()->id()],
            ['choice' => $data['choice']]
        );

        return back();
    }
}
