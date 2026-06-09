<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TriviaVote extends Model
{
    protected $fillable = [
        'trivia_question_id', 'user_id', 'choice',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(TriviaQuestion::class, 'trivia_question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
