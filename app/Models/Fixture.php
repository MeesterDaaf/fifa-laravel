<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fixture extends Model
{
    /** Aantal minuten vóór aftrap dat voorspellingen sluiten. */
    public const LOCK_MINUTES = 15;

    protected $fillable = [
        'external_id', 'home_team', 'away_team', 'home_team_code', 'away_team_code',
        'scheduled_at', 'stage', 'match_group', 'status',
        'home_score', 'away_score', 'first_goal_minute',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    public function isOpen(): bool
    {
        // Sluit LOCK_MINUTES vóór de aftrap: open zolang nu + 15 min < aftrap.
        return $this->status === 'SCHEDULED'
            && now()->addMinutes(self::LOCK_MINUTES)->lt($this->scheduled_at);
    }

    public function isFinished(): bool
    {
        return $this->status === 'FINISHED';
    }

    /** Tijdstip waarop voorspellen sluit (15 min vóór aftrap). */
    public function locksAt(): \Carbon\Carbon
    {
        return $this->scheduled_at->copy()->subMinutes(self::LOCK_MINUTES);
    }

    /** Wedstrijden waarvoor nog voorspeld mag worden. */
    public function scopeOpenForPredictions($query)
    {
        return $query->where('status', 'SCHEDULED')
            ->where('scheduled_at', '>', now()->addMinutes(self::LOCK_MINUTES));
    }

    /** Open wedstrijden mét bekende teams (TBD/knock-out zonder loting tellen niet mee). */
    public function scopeOpenWithTeams($query)
    {
        return $query->openForPredictions()
            ->where('home_team_code', '!=', 'TBD')
            ->where('away_team_code', '!=', 'TBD');
    }

    /** Gespeelde wedstrijden waarvan de uitslag nog niet is ingevoerd (>2u na aftrap, nog SCHEDULED). */
    public function scopeAwaitingResult($query)
    {
        return $query->where('status', 'SCHEDULED')
            ->where('scheduled_at', '<', now()->subHours(2));
    }

    public function stageLabel(): string
    {
        return match ($this->stage) {
            'GROUP_STAGE'    => 'Groepsfase',
            'LAST_32'        => 'Zestiende finale',
            'LAST_16'        => 'Achtste finale',
            'QUARTER_FINALS' => 'Kwartfinale',
            'SEMI_FINALS'    => 'Halve finale',
            'THIRD_PLACE'    => 'Derde plaats',
            'FINAL'          => 'Finale',
            default          => $this->stage,
        };
    }
}
