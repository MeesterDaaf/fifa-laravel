<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Genereert een korte Nederlandse onderbouwing via de Claude API.
 * Valt stil terug op null bij ontbrekende key of een API-fout — de
 * voorspellingen (cijfers) blijven dan gewoon werken.
 */
class AiReasoningService
{
    private const PERSONA = 'Je bent een eigenwijze maar deskundige Nederlandse voetbalanalist '
        .'in een voorspelpool. Je geeft korte, gevatte onderbouwingen bij voorspelde uitslagen. '
        .'Schrijf altijd in het Nederlands, maximaal één zin, zonder aanhef en zonder aanhalingstekens.';

    public function enabled(): bool
    {
        return filled(config('services.anthropic.key'));
    }

    public function reason(string $prompt): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(20)->post('https://api.anthropic.com/v1/messages', [
                'model'      => config('services.anthropic.model'),
                'max_tokens' => 150,
                // Persona als cache_control → goedkoper over de hele batch heen.
                'system'     => [[
                    'type'          => 'text',
                    'text'          => self::PERSONA,
                    'cache_control' => ['type' => 'ephemeral'],
                ]],
                'messages'   => [[
                    'role'    => 'user',
                    'content' => $prompt,
                ]],
            ]);

            if (! $response->successful()) {
                Log::warning('AI-onderbouwing mislukt', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $text = trim((string) $response->json('content.0.text', ''));

            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            Log::warning('AI-onderbouwing exception: '.$e->getMessage());
            return null;
        }
    }
}
