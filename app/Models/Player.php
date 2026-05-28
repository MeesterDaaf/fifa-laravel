<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = ['id', 'team_id', 'name', 'position', 'nationality', 'date_of_birth'];

    protected $casts = ['date_of_birth' => 'date'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** Brede positiecategorie voor weergave/sortering. */
    public function positionGroup(): string
    {
        return match ($this->position) {
            'Goalkeeper' => 'Keeper',
            'Centre-Back', 'Left-Back', 'Right-Back', 'Defence' => 'Verdediger',
            'Defensive Midfield', 'Central Midfield', 'Attacking Midfield',
            'Midfield', 'Left Midfield', 'Right Midfield' => 'Middenvelder',
            'Centre-Forward', 'Left Winger', 'Right Winger', 'Offence' => 'Aanvaller',
            default => 'Overig',
        };
    }

    /** Sorteervolgorde: aanvallers eerst (meest waarschijnlijke topscorers). */
    public function positionRank(): int
    {
        return match ($this->positionGroup()) {
            'Aanvaller'    => 0,
            'Middenvelder' => 1,
            'Verdediger'   => 2,
            'Keeper'       => 3,
            default        => 4,
        };
    }
}
