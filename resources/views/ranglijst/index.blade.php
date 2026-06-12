@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 stagger">

    <p class="kicker mb-1">Klassement</p>
    <h1 class="h-display text-4xl mb-2">Rang<span class="text-volt-400">lijst</span></h1>
    <p class="text-white/55 text-sm mb-6">
        {{ $totalMatches }} wedstrijd{{ $totalMatches !== 1 ? 'en' : '' }} afgespeeld
        @if($totalMatches > 0)
            <span class="text-white/35">· <span class="text-volt-400">▲</span>/<span class="text-signal-red">▼</span> = beweging door de laatst afgeronde wedstrijd</span>
        @endif
    </p>

    {{-- Jouw positie --}}
    @if($myPos !== false)
        <div class="card-volt p-4 mb-6">
            <p class="text-sm text-white/75">
                Jij staat <strong class="scoreline text-volt-400 text-lg">#{{ $myPos + 1 }}</strong> van de {{ count($leaderboard) }} deelnemers
                met <strong class="text-volt-400">{{ $leaderboard[$myPos]['totalPoints'] }} punten</strong>
            </p>
        </div>
    @endif

    @if(empty($leaderboard))
        <div class="card p-8 text-center">
            <div class="text-4xl mb-3">🏆</div>
            <p class="text-white/60">Nog geen deelnemers of punten</p>
        </div>
    @else
        <div class="card overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center gap-3 px-4 py-2 bg-white/3 border-b border-white/8 text-xs font-display font-bold text-white/40 uppercase tracking-wider">
                <span class="w-7">#</span>
                <span class="flex-1">Naam</span>
                <span class="hidden sm:block w-16 text-right">Wedstr.</span>
                <span class="hidden sm:block w-16 text-right">Toern.</span>
                <span class="w-16 text-right text-white/70">Totaal</span>
                <span class="w-2"></span>
            </div>

            @foreach($leaderboard as $i => $entry)
                <a href="/deelnemers/{{ $entry['id'] }}" class="row {{ $entry['id'] === $myId ? 'row-me' : '' }}">

                    <div class="w-7 shrink-0">
                        @if($i < 3)
                            <span class="rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span>
                        @else
                            <span class="text-sm text-white/45 scoreline pl-1.5">{{ $i + 1 }}</span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-white/90 text-sm truncate block">
                            {{ $entry['name'] }}
                            @if($entry['id'] === $myId)
                                <span class="text-volt-400 text-xs ml-1">(jij)</span>
                            @endif
                        </span>
                        <span class="text-xs text-white/40">
                            {{-- Op mobiel passen de losse puntenkolommen niet; toon de uitsplitsing hier --}}
                            <span class="sm:hidden">{{ $entry['matchPoints'] }}pt wedstr. · {{ $entry['tournamentPoints'] }}pt toern.</span>
                            <span class="hidden sm:inline">{{ $entry['predictionsCount'] }} voorspelling{{ $entry['predictionsCount'] !== 1 ? 'en' : '' }}</span>
                            @if(!is_null($entry['movement']) && $entry['movement'] !== 0)
                                <span class="ml-1 font-semibold {{ $entry['movement'] > 0 ? 'text-volt-400' : 'text-signal-red' }}">
                                    {{ $entry['movement'] > 0 ? '▲' : '▼' }}{{ abs($entry['movement']) }}
                                </span>
                            @endif
                        </span>
                    </div>

                    <span class="hidden sm:block w-16 text-right text-sm text-white/55 scoreline">{{ $entry['matchPoints'] }}pt</span>
                    <span class="hidden sm:block w-16 text-right text-sm text-white/55 scoreline">{{ $entry['tournamentPoints'] }}pt</span>
                    <span class="w-16 text-right scoreline text-lg {{ $i === 0 ? 'text-gold-400' : 'text-volt-400' }}">
                        {{ $entry['totalPoints'] }}pt
                    </span>
                    <span class="w-2 text-center text-white/20 shrink-0" aria-hidden="true">›</span>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Puntensysteem legenda --}}
    <div class="mt-8 card p-4">
        <h2 class="font-display font-bold uppercase tracking-wide text-white/80 mb-3 text-sm">📋 Puntensysteem</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-white/60">
            <div class="flex items-start gap-2">
                <span class="text-base">⚽</span>
                <div><p class="font-medium text-white/85">Exacte uitslag</p><p class="text-white/40">{{ config('scoring.match.exact') }} punten</p></div>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-base">✅</span>
                <div><p class="font-medium text-white/85">Juiste winnaar/gelijkspel</p><p class="text-white/40">{{ config('scoring.match.outcome') }} punten</p></div>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-base">🕐</span>
                <div><p class="font-medium text-white/85">Dichtstbijzijnde 1e doelpunt</p><p class="text-white/40">+{{ config('scoring.match.goal_minute_bonus') }} bonuspunten</p></div>
            </div>
        </div>

        <h2 class="font-display font-bold uppercase tracking-wide text-white/80 mb-3 mt-5 text-sm">🏆 Toernooi-bonus</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-white/60">
            <div class="flex items-start gap-2">
                <span class="text-base">🏆</span>
                <div><p class="font-medium text-white/85">Juiste toernooiwinnaar</p><p class="text-white/40">{{ config('scoring.tournament.champion') }} punten</p></div>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-base">🥇</span>
                <div><p class="font-medium text-white/85">Juiste topscorer WK</p><p class="text-white/40">{{ config('scoring.tournament.top_scorer') }} punten</p></div>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-base">🟨</span>
                <div><p class="font-medium text-white/85">Dichtst bij totaal gele kaarten</p><p class="text-white/40">{{ config('scoring.tournament.yellow_cards') }} punten</p></div>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-base">🟥</span>
                <div><p class="font-medium text-white/85">Dichtst bij totaal rode kaarten</p><p class="text-white/40">{{ config('scoring.tournament.red_cards') }} punten</p></div>
            </div>
        </div>
    </div>

</div>
@endsection
