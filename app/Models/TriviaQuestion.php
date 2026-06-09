<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TriviaQuestion extends Model
{
    protected $fillable = [
        'category', 'question', 'options', 'correct_index', 'explanation', 'used_on',
    ];

    protected $casts = [
        'options'  => 'array',
        'used_on'  => 'date',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(TriviaVote::class);
    }
}
