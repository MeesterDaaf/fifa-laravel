@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 stagger">

    <p class="kicker mb-1">Bonuspunten</p>
    <h1 class="h-display text-4xl mb-2">Toernooi<span class="text-volt-400">voorspelling</span></h1>
    <p class="text-white/55 text-sm mb-6">Voorspel de grote toernooi-uitslagen. Hoe meer goed, hoe meer bonuspunten!</p>

    {{-- Deadline-status --}}
    @if(! $isOpen)
        <div class="alert alert-error mb-6">
            <p class="text-sm font-semibold">🔒 De toernooivoorspelling is gesloten</p>
            <p class="text-xs text-white/60 mt-1">
                Het toernooi is begonnen{{ $deadline ? ' op '.format_day($deadline).' om '.to_nl_time($deadline)->format('H:i') : '' }}.
                Je voorspelling kan niet meer gewijzigd worden.
            </p>
        </div>
    @elseif($deadline)
        <div class="alert alert-warn mb-6">
            <p class="text-sm font-semibold">⏳ Vul op tijd in!</p>
            <p class="text-xs text-white/60 mt-1">
                Je kunt je toernooivoorspelling aanpassen tot de aftrap van de eerste wedstrijd:
                <strong class="text-signal-amber">{{ format_day($deadline) }} om {{ to_nl_time($deadline)->format('H:i') }}</strong>. Daarna sluit het definitief.
            </p>
        </div>
    @endif

    {{-- Puntensysteem --}}
    <div class="alert alert-info mb-6">
        <p class="font-display font-bold uppercase tracking-wide text-sm mb-2">🎯 Toernooi punten</p>
        <ul class="text-xs text-white/60 space-y-1">
            <li>🏆 Juiste toernooiwinnaar: <strong class="text-white/85">{{ config('scoring.tournament.champion') }} punten</strong></li>
            <li>🥇 Juiste topscorer: <strong class="text-white/85">{{ config('scoring.tournament.top_scorer') }} punten</strong></li>
            <li>🟨 Dichtst bij totaal gele kaarten: <strong class="text-white/85">{{ config('scoring.tournament.yellow_cards') }} punten</strong></li>
            <li>🟥 Dichtst bij totaal rode kaarten: <strong class="text-white/85">{{ config('scoring.tournament.red_cards') }} punten</strong></li>
        </ul>
    </div>

    {{-- Officiële uitslagen (indien bekend) --}}
    @php
        $hasResult = $tournamentResult && (
            $tournamentResult->top_scorer || $tournamentResult->champion ||
            $tournamentResult->total_yellow_cards !== null || $tournamentResult->total_red_cards !== null
        );
    @endphp
    @if($hasResult)
        <div class="card-volt p-4 mb-6">
            <p class="font-display font-bold uppercase tracking-wide text-sm text-gold-400 mb-2">📣 Officiële uitslagen</p>
            <ul class="text-xs text-white/70 space-y-1">
                @if($tournamentResult->champion)
                    <li>🏆 Winnaar: <strong class="text-white">{{ $tournamentResult->champion }}</strong></li>
                @endif
                @if($tournamentResult->top_scorer)
                    <li>🥇 Topscorer: <strong class="text-white">{{ $tournamentResult->top_scorer }}</strong></li>
                @endif
                @if($tournamentResult->total_yellow_cards !== null)
                    <li>🟨 Gele kaarten: <strong class="text-white">{{ $tournamentResult->total_yellow_cards }}</strong></li>
                @endif
                @if($tournamentResult->total_red_cards !== null)
                    <li>🟥 Rode kaarten: <strong class="text-white">{{ $tournamentResult->total_red_cards }}</strong></li>
                @endif
            </ul>
            @if($myPrediction)
                <p class="text-xs text-white/60 mt-2 pt-2 border-t border-white/10">
                    Jouw toernooipunten: <strong class="text-volt-400">{{ $myPrediction->points }} punten</strong>
                    @if($myPrediction->points > 0)
                        ({{ collect([
                            $myPrediction->points_champion > 0 ? "winnaar +{$myPrediction->points_champion}" : null,
                            $myPrediction->points_top_scorer > 0 ? "topscorer +{$myPrediction->points_top_scorer}" : null,
                            $myPrediction->points_yellow > 0 ? "geel +{$myPrediction->points_yellow}" : null,
                            $myPrediction->points_red > 0 ? "rood +{$myPrediction->points_red}" : null,
                        ])->filter()->join(', ') }}) 🎉
                    @endif
                </p>
            @endif
        </div>
    @endif

    {{-- Jouw huidige voorspellingen --}}
    <div class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-white mb-3">Jouw voorspellingen</h2>
        <div class="grid grid-cols-2 gap-3 text-sm">
            @foreach([
                ['🏆 Winnaar', $myPrediction?->champion],
                ['🥇 Topscorer', $myPrediction?->top_scorer],
                ['🟨 Gele kaarten', $myPrediction?->total_yellow_cards],
                ['🟥 Rode kaarten', $myPrediction?->total_red_cards],
            ] as [$label, $value])
                <div class="bg-white/4 border border-white/8 rounded-xl p-3">
                    <div class="text-xs text-white/45 mb-0.5">{{ $label }}</div>
                    <div class="font-display font-bold text-base {{ $value !== null ? 'text-volt-300' : 'text-white/30' }}">{{ $value ?? '—' }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Winnaar + kaarten formulier --}}
    <div class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-white mb-4">🏆 Winnaar &amp; kaarten</h2>
        <form method="POST" action="/toernooi" class="space-y-4">
            @csrf

            <div>
                <label class="label">Welk land wint het toernooi?</label>
                <select name="champion" @disabled(! $isOpen) class="input">
                    <option value="">— Kies een land —</option>
                    @foreach($teams as $team)
                        @php $teamName = country_name($team->tla, $team->name); @endphp
                        <option value="{{ $teamName }}" {{ $myPrediction?->champion === $teamName ? 'selected' : '' }}>
                            {{ get_flag($team->tla) }} {{ $teamName }}
                        </option>
                    @endforeach
                </select>
                @if($teams->isEmpty())
                    <p class="text-xs text-white/40 mt-1">Landenlijst nog niet geladen — vraag de admin om te synchroniseren.</p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label">🟨 Totaal gele kaarten</label>
                    <input type="number" inputmode="numeric" name="total_yellow_cards" min="0" max="2000" @disabled(! $isOpen)
                        value="{{ old('total_yellow_cards', $myPrediction?->total_yellow_cards) }}"
                        placeholder="bv. 220" class="input">
                </div>
                <div>
                    <label class="label">🟥 Totaal rode kaarten</label>
                    <input type="number" inputmode="numeric" name="total_red_cards" min="0" max="500" @disabled(! $isOpen)
                        value="{{ old('total_red_cards', $myPrediction?->total_red_cards) }}"
                        placeholder="bv. 12" class="input">
                </div>
            </div>

            <button type="submit" @disabled(! $isOpen) class="btn btn-volt w-full py-3">
                {{ $isOpen ? '💾 Opslaan' : '🔒 Gesloten' }}
            </button>
        </form>
    </div>

    {{-- Topscorer picker --}}
    <h2 class="font-display font-bold uppercase tracking-wide text-white mb-3">🥇 Kies je topscorer</h2>
    <div id="picker" class="card p-6 mb-8 scroll-mt-20">
        @if($teams->isEmpty())
            <div class="text-center text-white/50 text-sm py-4">
                <div class="text-3xl mb-2">📭</div>
                Nog geen spelers geladen. Vraag de admin om <strong class="text-white/80">teams &amp; spelers te synchroniseren</strong>.
            </div>
        @elseif(! $selectedTeam)
            {{-- Stap 1: kies een land --}}
            <p class="text-sm text-white/50 mb-4">Klik een land aan om de selectie te zien.</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($teams as $team)
                    <a href="/toernooi?team={{ $team->tla }}#picker"
                        class="flex items-center gap-2 px-3 py-2.5 rounded-xl border border-white/8 bg-white/3 hover:border-volt-500/50 hover:bg-volt-500/8 transition-all">
                        <span class="text-xl">{{ get_flag($team->tla) }}</span>
                        <span class="text-sm font-medium text-white/85 truncate">{{ country_name($team->tla, $team->name) }}</span>
                    </a>
                @endforeach
            </div>
        @else
            {{-- Stap 2: kies een speler --}}
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display font-bold uppercase tracking-wide text-white flex items-center gap-2">
                    <span class="text-2xl">{{ get_flag($selectedTeam->tla) }}</span>
                    {{ country_name($selectedTeam->tla, $selectedTeam->name) }}
                </h3>
                <a href="/toernooi#picker" class="text-sm text-volt-400 hover:text-volt-300">← Ander land</a>
            </div>

            @foreach($squad as $group => $players)
                <div class="mb-4">
                    <h4 class="text-xs font-display font-bold text-white/35 uppercase tracking-[0.15em] mb-2">{{ $group }}</h4>
                    <div class="grid sm:grid-cols-2 gap-2">
                        @foreach($players as $player)
                            <form method="POST" action="/toernooi">
                                @csrf
                                <input type="hidden" name="top_scorer" value="{{ $player->name }}">
                                <button type="submit" @disabled(! $isOpen)
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl border text-left transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer
                                        {{ $myPrediction?->top_scorer === $player->name
                                            ? 'border-volt-500/60 bg-volt-500/10'
                                            : 'border-white/8 bg-white/3 hover:border-volt-500/50 hover:bg-volt-500/8' }}">
                                    <span class="text-sm font-medium text-white/85 truncate">{{ $player->name }}</span>
                                    @if($myPrediction?->top_scorer === $player->name)
                                        <span class="text-volt-400 text-xs flex-shrink-0">✓ gekozen</span>
                                    @else
                                        <span class="text-white/25 text-xs flex-shrink-0">kies →</span>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Overzicht alle voorspellingen --}}
    @if($allPredictions->isNotEmpty())
        <div>
            <h2 class="font-display font-bold uppercase tracking-wide text-white mb-3">👥 Voorspellingen van iedereen</h2>
            <div class="card overflow-hidden">
                @foreach($allPredictions as $pred)
                    <div class="px-4 py-3 border-b border-white/5 last:border-0
                        {{ $pred->user_id === auth()->id() ? 'row-me' : '' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-white/90">
                                {{ $pred->user->name }}
                                @if($pred->user_id === auth()->id())
                                    <span class="text-volt-400 text-xs ml-1">(jij)</span>
                                @endif
                            </span>
                            @if($hasResult)
                                <span class="text-xs scoreline {{ $pred->points > 0 ? 'text-volt-400' : 'text-white/35' }}">
                                    {{ $pred->points > 0 ? "+{$pred->points}pt 🎉" : '0pt' }}
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-white/50 mt-1 flex flex-wrap gap-x-3 gap-y-0.5">
                            <span class="{{ $pred->points_champion > 0 ? 'text-volt-400 font-semibold' : '' }}">
                                🏆 {{ $pred->champion ?? '—' }}@if($pred->points_champion > 0) +{{ $pred->points_champion }}@endif
                            </span>
                            <span class="{{ $pred->points_top_scorer > 0 ? 'text-volt-400 font-semibold' : '' }}">
                                🥇 {{ $pred->top_scorer ?? '—' }}@if($pred->points_top_scorer > 0) +{{ $pred->points_top_scorer }}@endif
                            </span>
                            <span class="{{ $pred->points_yellow > 0 ? 'text-volt-400 font-semibold' : '' }}">
                                🟨 {{ $pred->total_yellow_cards ?? '—' }}@if($pred->points_yellow > 0) 🎯+{{ $pred->points_yellow }}@endif
                            </span>
                            <span class="{{ $pred->points_red > 0 ? 'text-volt-400 font-semibold' : '' }}">
                                🟥 {{ $pred->total_red_cards ?? '—' }}@if($pred->points_red > 0) 🎯+{{ $pred->points_red }}@endif
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
