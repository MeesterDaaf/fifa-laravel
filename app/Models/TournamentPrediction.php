<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentPrediction extends Model
{
    protected $fillable = ['user_id', 'top_scorer', 'points'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
