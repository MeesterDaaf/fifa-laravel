<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fixture extends Model
{
    protected $fillable = [
        'external_id', 'home_team', 'away_team', 'home_team_code', 'away_team_code',
        'scheduled_at', 'stage', 'match_group', 'status',
        'home_score', 'away_score', 'first_goal_minute', 'first_yellow_card_minute',
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
        return $this->status === 'SCHEDULED' && now()->lt($this->scheduled_at);
    }

    public function isFinished(): bool
    {
        return $this->status === 'FINISHED';
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
