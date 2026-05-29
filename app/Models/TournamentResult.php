<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentResult extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'top_scorer', 'total_yellow_cards', 'total_red_cards', 'champion'];
}
