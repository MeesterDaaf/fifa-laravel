<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'invite_code', 'whatsapp_group_url', 'ranking_captured_at'];

    protected $casts = ['ranking_captured_at' => 'datetime'];

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

    public static function whatsappGroupUrl(): ?string
    {
        return static::firstOrCreate(['id' => 'singleton'])->whatsapp_group_url;
    }

    public static function setWhatsappGroupUrl(?string $url): void
    {
        static::updateOrCreate(['id' => 'singleton'], ['whatsapp_group_url' => $url]);
    }
}
