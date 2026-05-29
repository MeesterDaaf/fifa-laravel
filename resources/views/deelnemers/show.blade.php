@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">

    <a href="/deelnemers" class="text-sm text-green-600 hover:underline">← Alle deelnemers</a>

    <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-1">
        {{ $user->name }}
        @if($user->id === auth()->id())
            <span class="text-green-600 text-base font-medium">(jij)</span>
        @endif
    </h1>
    <p class="text-gray-500 text-sm mb-6">Voorspellingen van {{ $user->name }}</p>

    {{-- Toernooi-voorspellingen --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="font-semibold text-gray-700 mb-3">🏆 Toernooi</h2>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400">🏆 Winnaar</div>
                <div class="font-bold text-gray-800">{{ $tournament?->champion ?? '—' }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400">🥇 Topscorer</div>
                <div class="font-bold text-gray-800">{{ $tournament?->top_scorer ?? '—' }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400">🟨 Gele kaarten</div>
                <div class="font-bold text-gray-800">{{ $tournament?->total_yellow_cards ?? '—' }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400">🟥 Rode kaarten</div>
                <div class="font-bold text-gray-800">{{ $tournament?->total_red_cards ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Wedstrijd-voorspellingen per fase --}}
    <h2 class="font-semibold text-gray-700 mb-3">⚽ Wedstrijden</h2>

    @php
        $stageOrder = ['GROUP_STAGE', 'LAST_32', 'LAST_16', 'QUARTER_FINALS', 'SEMI_FINALS', 'THIRD_PLACE', 'FINAL'];
        $sorted = $byStage->sortBy(fn($_, $k) => array_search($k, $stageOrder) !== false ? array_search($k, $stageOrder) : 99);
    @endphp

    @if($byStage->isEmpty())
        <p class="text-gray-500 text-sm bg-white rounded-xl p-4 shadow-sm">Nog geen wedstrijden geladen.</p>
    @endif

    @foreach($sorted as $stage => $matches)
        <section class="mb-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                {{ $matches->first()->stageLabel() }}
            </h3>
            <div class="space-y-2">
                @foreach($matches as $match)
                    @php $pred = $predictions->get($match->id); @endphp
                    <div class="bg-white rounded-xl px-4 py-3 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 flex items-center gap-2 min-w-0">
                                <span class="flex-1 text-sm font-semibold text-gray-800 truncate">
                                    {{ get_flag($match->home_team_code) }} {{ country_name($match->home_team_code, $match->home_team) }}
                                </span>
                                @if($match->isFinished())
                                    <span class="shrink-0 bg-gray-800 text-white text-xs font-bold px-2 py-0.5 rounded">
                                        {{ $match->home_score }}-{{ $match->away_score }}
                                    </span>
                                @else
                                    <span class="shrink-0 text-gray-300 text-xs">vs</span>
                                @endif
                                <span class="flex-1 text-sm font-semibold text-gray-800 truncate text-right">
                                    {{ country_name($match->away_team_code, $match->away_team) }} {{ get_flag($match->away_team_code) }}
                                </span>
                            </div>

                            <div class="text-right flex-shrink-0 w-20">
                                @if($pred)
                                    <span class="text-sm font-bold text-green-700">{{ $pred->home_score }}-{{ $pred->away_score }}</span>
                                    @if($match->isFinished())
                                        <div class="text-xs text-gray-400">{{ $pred->total_points }}pt</div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </div>
                        </div>

                        @if($pred && $pred->first_goal_minute !== null)
                            <div class="mt-1 text-xs text-gray-400">⚽ 1e doelpunt: minuut {{ $pred->first_goal_minute }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach

</div>
@endsection
