@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 space-y-8">

    {{-- Hero --}}
    <div class="bg-gradient-to-r from-green-700 to-green-600 rounded-2xl p-6 text-white">
        <h1 class="text-2xl font-bold">Welkom, {{ auth()->user()->name }}! 👋</h1>
        <p class="text-green-200 mt-1">FIFA Wereldkampioenschap 2026</p>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="/voorspellingen" class="bg-white text-green-700 font-semibold px-5 py-2.5 rounded-xl hover:bg-green-50 transition-colors text-sm">
                ⚽ Maak voorspelling
            </a>
            <a href="/toernooi" class="bg-green-600 border border-white/30 text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-green-500 transition-colors text-sm">
                🏆 Toernooi voorspelling
            </a>
            @if($whatsappGroupUrl)
                <a href="{{ $whatsappGroupUrl }}" target="_blank" rel="noopener"
                    class="bg-[#25D366] text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-[#1ebe5d] transition-colors text-sm">
                    💬 WhatsApp-groep
                </a>
            @endif
        </div>
    </div>

    {{-- Voortgang: maak duidelijk dat je BEIDE moet doen --}}
    @php
        $matchesDone = $openCount > 0 && $predictedCount >= $openCount;
        $tournamentComplete = $tournamentDone === 4;
        $allDone = ($openCount === 0 || $matchesDone) && $tournamentComplete;
    @endphp

    <div class="rounded-2xl border p-5 {{ $allDone ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200' }}">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xl">{{ $allDone ? '✅' : '📋' }}</span>
            <h2 class="font-bold text-gray-800">
                {{ $allDone ? 'Je bent helemaal bij — alles voorspeld!' : 'Maak je voorspellingen compleet' }}
            </h2>
        </div>

        @unless($allDone)
            <p class="text-sm text-gray-600 mb-4">
                Je doet mee op twee manieren: voorspel de <strong>wedstrijden</strong> én maak je <strong>toernooi-voorspellingen</strong>. Vergeet geen van beide!
            </p>
        @endunless

        <div class="grid sm:grid-cols-2 gap-3">

            {{-- Wedstrijden --}}
            <a href="/voorspellingen" class="block bg-white rounded-xl p-4 border border-gray-100 hover:border-green-300 hover:shadow-sm transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-gray-800 text-sm">⚽ Wedstrijden</span>
                    @if($openCount === 0)
                        <span class="text-xs text-gray-400">geen open</span>
                    @elseif($matchesDone)
                        <span class="text-xs font-medium text-green-600">✅ compleet</span>
                    @else
                        <span class="text-xs font-medium text-amber-600">{{ $openCount - $predictedCount }} te doen</span>
                    @endif
                </div>
                @if($openCount > 0)
                    <div class="flex h-2 rounded-full overflow-hidden bg-gray-100 mb-1">
                        <div class="bg-green-500" style="width: {{ round($predictedCount / $openCount * 100) }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500">{{ $predictedCount }} van {{ $openCount }} open wedstrijden voorspeld</p>
                @else
                    <p class="text-xs text-gray-500">Er staan nu geen wedstrijden open om te voorspellen.</p>
                @endif
            </a>

            {{-- Toernooi --}}
            <a href="/toernooi" class="block bg-white rounded-xl p-4 border border-gray-100 hover:border-green-300 hover:shadow-sm transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-gray-800 text-sm">🏆 Toernooi</span>
                    @if($tournamentComplete)
                        <span class="text-xs font-medium text-green-600">✅ compleet</span>
                    @else
                        <span class="text-xs font-medium text-amber-600">{{ $tournamentDone }}/4 gedaan</span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @foreach([
                        'champion' => 'Winnaar',
                        'top_scorer' => 'Topscorer',
                        'yellow' => 'Gele kaarten',
                        'red' => 'Rode kaarten',
                    ] as $key => $label)
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $tournamentStatus[$key] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $tournamentStatus[$key] ? '✅' : '⭕' }} {{ $label }}
                        </span>
                    @endforeach
                </div>
            </a>

        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">

        {{-- Aankomende wedstrijden --}}
        <section>
            <h2 class="text-lg font-bold text-gray-800 mb-3">📅 Aankomende wedstrijden</h2>
            @if($upcoming->isEmpty())
                <p class="text-gray-500 text-sm bg-white rounded-xl p-4 shadow-sm">
                    Geen geplande wedstrijden. Sync eerst wedstrijden via admin.
                </p>
            @else
                <div class="space-y-2">
                    @foreach($upcoming as $match)
                        <a href="/voorspellingen/{{ $match->id }}" class="block bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:border-green-300 hover:shadow-md transition-all">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <span class="text-sm font-semibold text-gray-800 truncate">
                                        {{ get_flag($match->home_team_code) }} {{ country_name($match->home_team_code, $match->home_team) }}
                                    </span>
                                    <span class="text-gray-400 text-xs font-bold shrink-0">vs</span>
                                    <span class="text-sm font-semibold text-gray-800 truncate text-right">
                                        {{ country_name($match->away_team_code, $match->away_team) }} {{ get_flag($match->away_team_code) }}
                                    </span>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-xs text-gray-500">{{ format_date_short($match->scheduled_at) }}</div>
                                    @if(isset($myPredIds[$match->id]))
                                        <span class="text-xs text-green-600 font-medium">✅ Voorspeld</span>
                                    @else
                                        <span class="text-xs text-orange-500 font-medium">⏳ Voorspel nog</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                    <a href="/voorspellingen" class="block text-center text-sm text-green-600 hover:underline py-1">
                        Alle wedstrijden →
                    </a>
                </div>
            @endif
        </section>

        {{-- Top 5 Ranglijst --}}
        <section>
            <h2 class="text-lg font-bold text-gray-800 mb-3">🏆 Top 5 Ranglijst</h2>
            @if(empty($leaderboard))
                <p class="text-gray-500 text-sm bg-white rounded-xl p-4 shadow-sm">Nog geen punten gescoord</p>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    @foreach($leaderboard as $i => $entry)
                        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 last:border-0 {{ $entry['id'] === auth()->id() ? 'bg-green-50' : '' }}">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0
                                {{ $i === 0 ? 'bg-yellow-400 text-yellow-900' : ($i === 1 ? 'bg-gray-300 text-gray-700' : ($i === 2 ? 'bg-orange-300 text-orange-900' : 'bg-gray-100 text-gray-600')) }}">
                                {{ $i + 1 }}
                            </span>
                            <span class="flex-1 font-medium text-sm text-gray-800 truncate">
                                {{ $entry['name'] }}
                                @if($entry['id'] === auth()->id())
                                    <span class="text-green-600 text-xs ml-1">(jij)</span>
                                @endif
                            </span>
                            <span class="font-bold text-green-700 flex-shrink-0">{{ $entry['totalPoints'] }}pt</span>
                        </div>
                    @endforeach
                    <a href="/ranglijst" class="block text-center text-sm text-green-600 hover:underline py-3">
                        Volledige ranglijst →
                    </a>
                </div>
            @endif
        </section>

    </div>

    {{-- Recente uitslagen --}}
    @if($recent->isNotEmpty())
        <section>
            <h2 class="text-lg font-bold text-gray-800 mb-3">🎯 Recente uitslagen</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($recent as $match)
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="text-xs text-gray-400 mb-2">{{ format_date_short($match->scheduled_at) }}</div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-medium text-gray-700">
                                {{ get_flag($match->home_team_code) }} {{ $match->home_team_code }}
                            </span>
                            <span class="text-lg font-bold text-gray-800 bg-gray-100 px-3 py-1 rounded-lg">
                                {{ $match->home_score ?? '?' }} - {{ $match->away_score ?? '?' }}
                            </span>
                            <span class="text-sm font-medium text-gray-700">
                                {{ $match->away_team_code }} {{ get_flag($match->away_team_code) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
