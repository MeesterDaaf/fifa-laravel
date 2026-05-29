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
