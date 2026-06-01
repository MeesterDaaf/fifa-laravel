@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold text-gray-800 mb-2">📊 Ranglijst</h1>
    <p class="text-gray-500 text-sm mb-6">
        {{ $totalMatches }} wedstrijd{{ $totalMatches !== 1 ? 'en' : '' }} afgespeeld
    </p>

    {{-- Jouw positie --}}
    @if($myPos !== false)
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
            <p class="text-sm text-green-700">
                Jij staat <strong>#{{ $myPos + 1 }}</strong> van de {{ count($leaderboard) }} deelnemers
                met <strong>{{ $leaderboard[$myPos]['totalPoints'] }} punten</strong>
            </p>
        </div>
    @endif

    @if(empty($leaderboard))
        <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
            <div class="text-4xl mb-3">🏆</div>
            <p class="text-gray-500">Nog geen deelnemers of punten</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center gap-3 px-4 py-2 bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <span class="w-7">#</span>
                <span class="flex-1">Naam</span>
                <span class="w-16 text-right">Wedstr.</span>
                <span class="w-16 text-right">Toern.</span>
                <span class="w-16 text-right font-bold text-gray-700">Totaal</span>
                <span class="w-2"></span>
            </div>

            @foreach($leaderboard as $i => $entry)
                <a href="/deelnemers/{{ $entry['id'] }}" class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0 transition-colors
                    {{ $entry['id'] === $myId ? 'bg-green-50' : 'hover:bg-gray-50' }}">

                    <div class="w-7 flex-shrink-0">
                        @if($i < 3)
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold
                                {{ $i === 0 ? 'bg-yellow-400 text-yellow-900' : ($i === 1 ? 'bg-gray-300 text-gray-700' : 'bg-orange-300 text-orange-900') }}">
                                {{ $i + 1 }}
                            </span>
                        @else
                            <span class="text-sm text-gray-500 font-medium pl-1">{{ $i + 1 }}</span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-gray-800 text-sm truncate block">
                            {{ $entry['name'] }}
                            @if($entry['id'] === $myId)
                                <span class="text-green-600 text-xs ml-1">(jij)</span>
                            @endif
                        </span>
                        <span class="text-xs text-gray-400">
                            {{ $entry['predictionsCount'] }} voorspelling{{ $entry['predictionsCount'] !== 1 ? 'en' : '' }}
                            @if(!is_null($entry['movement']) && $entry['movement'] !== 0)
                                <span class="ml-1 font-semibold {{ $entry['movement'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $entry['movement'] > 0 ? '▲' : '▼' }}{{ abs($entry['movement']) }}
                                </span>
                            @endif
                        </span>
                    </div>

                    <span class="w-16 text-right text-sm text-gray-600">{{ $entry['matchPoints'] }}pt</span>
                    <span class="w-16 text-right text-sm text-gray-600">{{ $entry['tournamentPoints'] }}pt</span>
                    <span class="w-16 text-right font-bold text-base {{ $i === 0 ? 'text-yellow-600' : 'text-green-700' }}">
                        {{ $entry['totalPoints'] }}pt
                    </span>
                    <span class="w-2 text-center text-gray-300 shrink-0" aria-hidden="true">›</span>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Puntensysteem legenda --}}
    <div class="mt-8 bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-700 mb-3 text-sm">📋 Puntensysteem</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-600">
            <div class="flex items-start gap-2">
                <span class="text-base">⚽</span>
                <div><p class="font-medium">Exacte uitslag</p><p class="text-gray-400">{{ config('scoring.match.exact') }} punten</p></div>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-base">✅</span>
                <div><p class="font-medium">Juiste winnaar/gelijkspel</p><p class="text-gray-400">{{ config('scoring.match.outcome') }} punten</p></div>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-base">🕐</span>
                <div><p class="font-medium">Dichtstbijzijnde 1e doelpunt</p><p class="text-gray-400">+{{ config('scoring.match.goal_minute_bonus') }} bonuspunten</p></div>
            </div>
        </div>

        <h2 class="font-semibold text-gray-700 mb-3 mt-5 text-sm">🏆 Toernooi-bonus</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-600">
            <div class="flex items-start gap-2">
                <span class="text-base">🏆</span>
                <div><p class="font-medium">Juiste toernooiwinnaar</p><p class="text-gray-400">{{ config('scoring.tournament.champion') }} punten</p></div>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-base">🥇</span>
                <div><p class="font-medium">Juiste topscorer WK</p><p class="text-gray-400">{{ config('scoring.tournament.top_scorer') }} punten</p></div>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-base">🟨</span>
                <div><p class="font-medium">Dichtst bij totaal gele kaarten</p><p class="text-gray-400">{{ config('scoring.tournament.yellow_cards') }} punten</p></div>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-base">🟥</span>
                <div><p class="font-medium">Dichtst bij totaal rode kaarten</p><p class="text-gray-400">{{ config('scoring.tournament.red_cards') }} punten</p></div>
            </div>
        </div>
    </div>

</div>
@endsection
