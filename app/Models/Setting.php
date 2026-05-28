<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'invite_code'];

    public static function inviteCode(): string
    {
        return static::firstOrCreate(
            ['id' => 'singleton'],
            ['invite_code' => (string) Str::uuid()]
        )->invite_code;
    }

    public static function regenerateInviteCode(): string
    {
        $code = (string) Str::uuid();
        static::updateOrCreate(['id' => 'singleton'], ['invite_code' => $code]);
        return $code;
    }
}
