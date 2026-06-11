@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 stagger">

    <a href="/ranglijst" class="text-sm text-volt-400 hover:text-volt-300">← Ranglijst</a>

    <p class="kicker mt-3 mb-1">Spelersprofiel</p>
    <h1 class="h-display text-4xl mb-1">
        {{ $user->name }}
        @if($user->id === auth()->id())
            <span class="text-volt-400 text-xl not-italic">(jij)</span>
        @endif
    </h1>
    <p class="text-white/55 text-sm mb-6">
        Voorspellingen van {{ $user->name }}
        @if(!$isOwner)
            <span class="block text-xs text-white/40 mt-1">🔒 Voorspellingen zijn pas zichtbaar zodra een wedstrijd op slot gaat</span>
        @endif
    </p>

    {{-- Toernooi-voorspellingen --}}
    <div class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-white mb-3">🏆 Toernooi</h2>
        @if($tournamentHidden)
            <p class="text-sm text-white/50 bg-white/4 border border-white/8 rounded-xl p-3">
                🔒 Verborgen tot de deadline — voorspellingen van anderen zijn pas zichtbaar als het toernooi begint.
            </p>
        @else
            <div class="grid grid-cols-2 gap-3 text-sm">
                @foreach([
                    ['🏆 Winnaar', $tournament?->champion],
                    ['🥇 Topscorer', $tournament?->top_scorer],
                    ['🟨 Gele kaarten', $tournament?->total_yellow_cards],
                    ['🟥 Rode kaarten', $tournament?->total_red_cards],
                ] as [$label, $value])
                    <div class="bg-white/4 border border-white/8 rounded-xl p-3 min-w-0">
                        <div class="text-xs text-white/45 mb-0.5">{{ $label }}</div>
                        <div class="font-display font-bold text-base break-words {{ $value !== null ? 'text-volt-300' : 'text-white/30' }}">{{ $value ?? '—' }}</div>
                    </div>
                @endforeach
            </div>
            @if($tournament?->ai_reasoning)
                <p class="mt-3 text-sm text-signal-blue bg-signal-blue/8 border border-signal-blue/20 rounded-lg px-3 py-2 italic">
                    🤖 {{ $tournament->ai_reasoning }}
                </p>
            @endif
        @endif
    </div>

    {{-- Wedstrijd-voorspellingen per fase --}}
    <h2 class="font-display font-bold uppercase tracking-wide text-white mb-3">⚽ Wedstrijden</h2>

    @php
        $stageOrder = ['GROUP_STAGE', 'LAST_32', 'LAST_16', 'QUARTER_FINALS', 'SEMI_FINALS', 'THIRD_PLACE', 'FINAL'];
        $sorted = $byStage->sortBy(fn($_, $k) => array_search($k, $stageOrder) !== false ? array_search($k, $stageOrder) : 99);
    @endphp

    @if($byStage->isEmpty())
        <p class="text-white/50 text-sm card p-4">Nog geen wedstrijden geladen.</p>
    @endif

    @foreach($sorted as $stage => $matches)
        <section class="mb-6">
            <h3 class="flex items-center gap-3 mb-2">
                <span class="font-display font-bold uppercase tracking-[0.15em] text-xs text-volt-500">{{ $matches->first()->stageLabel() }}</span>
                <span class="flex-1 h-px bg-gradient-to-r from-volt-500/30 to-transparent"></span>
            </h3>
            <div class="space-y-2">
                @foreach($matches as $match)
                    @php
                        $pred = $predictions->get($match->id);
                        $hidden = ! $isOwner && $match->isOpen();
                    @endphp
                    <div class="card px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 flex items-center gap-2 min-w-0">
                                <span class="flex-1 flex items-center gap-1 min-w-0 text-sm font-semibold text-white">
                                    <span class="shrink-0">{{ get_flag($match->home_team_code) }}</span>
                                    <span class="truncate">{{ country_name($match->home_team_code, $match->home_team) }}</span>
                                </span>
                                @if($match->isFinished())
                                    <span class="shrink-0 scorebox text-xs">
                                        {{ $match->home_score }}-{{ $match->away_score }}
                                    </span>
                                @else
                                    <span class="shrink-0 text-white/25 text-xs">vs</span>
                                @endif
                                <span class="flex-1 flex items-center justify-end gap-1 min-w-0 text-sm font-semibold text-white">
                                    <span class="truncate text-right">{{ country_name($match->away_team_code, $match->away_team) }}</span>
                                    <span class="shrink-0">{{ get_flag($match->away_team_code) }}</span>
                                </span>
                            </div>

                            <div class="text-right flex-shrink-0 w-20">
                                @if($hidden)
                                    <span class="text-xs text-white/25" title="Verborgen tot de wedstrijd op slot gaat">🔒</span>
                                @elseif($pred)
                                    <span class="text-sm scoreline text-volt-400">{{ $pred->home_score }}-{{ $pred->away_score }}</span>
                                    @if($match->isFinished())
                                        <div class="text-xs text-white/40">{{ $pred->total_points }}pt</div>
                                    @endif
                                @else
                                    <span class="text-xs text-white/25">—</span>
                                @endif
                            </div>
                        </div>

                        @if(!$hidden && $pred && $pred->first_goal_minute !== null)
                            <div class="mt-1 text-xs text-white/40">⚽ 1e doelpunt: minuut {{ $pred->first_goal_minute }}</div>
                        @endif
                        @if(!$hidden && $pred && $pred->ai_reasoning)
                            <p class="mt-1 text-xs text-signal-blue italic">🤖 {{ $pred->ai_reasoning }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach

</div>
@endsection
