<?php

namespace App\Models;

use Carbon\Carbon;
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

    /**
     * Deadline voor de toernooivoorspelling: de aftrap van de eerste wedstrijd
     * van het toernooi. Null zolang er nog geen wedstrijden geladen zijn.
     */
    public static function deadline(): ?Carbon
    {
        return Fixture::orderBy('scheduled_at')->first()?->scheduled_at;
    }

    /**
     * Of de toernooivoorspelling nog ingevuld/gewijzigd mag worden. Open zolang
     * de eerste wedstrijd nog niet is begonnen (of er nog geen wedstrijden zijn).
     */
    public static function isOpen(): bool
    {
        $deadline = self::deadline();

        return $deadline === null || now()->lt($deadline);
    }
}
