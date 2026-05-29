<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentPrediction extends Model
{
    protected $fillable = [
        'user_id', 'top_scorer', 'total_yellow_cards', 'total_red_cards', 'champion',
        'points', 'points_top_scorer', 'points_yellow', 'points_red', 'points_champion',
        'ai_reasoning',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
