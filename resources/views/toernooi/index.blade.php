@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold text-gray-800 mb-2">🏆 Toernooi Voorspelling</h1>
    <p class="text-gray-500 text-sm mb-6">Voorspel de topscorer van het WK 2026. Juiste naam = 10 punten!</p>

    {{-- Puntensysteem --}}
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
        <p class="font-semibold text-blue-800 text-sm mb-2">🎯 Toernooi punten</p>
        <ul class="text-xs text-blue-700 space-y-1">
            <li>🥇 Juiste topscorer: <strong>10 punten</strong></li>
        </ul>
    </div>

    {{-- Officiële topscorer --}}
    @if($tournamentResult?->top_scorer)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
            <p class="text-sm font-semibold text-yellow-800">
                🥇 Officiële topscorer: <span class="text-yellow-900">{{ $tournamentResult->top_scorer }}</span>
            </p>
            @if($myPrediction)
                <p class="text-xs text-yellow-700 mt-1">
                    Jouw voorspelling: {{ $myPrediction->top_scorer }} —
                    <strong>{{ $myPrediction->points > 0 ? "+{$myPrediction->points} punten! 🎉" : "Helaas, geen punten" }}</strong>
                </p>
            @endif
        </div>
    @endif

    {{-- Formulier --}}
    {{-- Huidige keuze --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="font-semibold text-gray-700 mb-2">Jouw topscorer</h2>
        @if($myPrediction)
            <div class="flex items-center gap-2">
                <span class="text-2xl">🥇</span>
                <span class="text-lg font-bold text-green-700">{{ $myPrediction->top_scorer }}</span>
            </div>
            <p class="text-xs text-gray-400 mt-1">Kies hieronder een ander land/speler om te wijzigen.</p>
        @else
            <p class="text-gray-500 text-sm">Je hebt nog geen topscorer gekozen. Kies een land en speler hieronder. 👇</p>
        @endif
    </div>

    {{-- Picker --}}
    <div id="picker" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8 scroll-mt-20">
        @if($teams->isEmpty())
            <div class="text-center text-gray-500 text-sm py-4">
                <div class="text-3xl mb-2">📭</div>
                Nog geen spelers geladen. Vraag de admin om <strong>teams &amp; spelers te synchroniseren</strong>.
            </div>
        @elseif(! $selectedTeam)
            {{-- Stap 1: kies een land --}}
            <h2 class="font-semibold text-gray-700 mb-4">🌍 Kies een land</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($teams as $team)
                    <a href="/toernooi?team={{ $team->tla }}#picker"
                        class="flex items-center gap-2 px-3 py-2.5 rounded-xl border border-gray-100 hover:border-green-300 hover:bg-green-50 transition-all">
                        <span class="text-xl">{{ get_flag($team->tla) }}</span>
                        <span class="text-sm font-medium text-gray-800 truncate">{{ $team->name }}</span>
                    </a>
                @endforeach
            </div>
        @else
            {{-- Stap 2: kies een speler --}}
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-700 flex items-center gap-2">
                    <span class="text-2xl">{{ get_flag($selectedTeam->tla) }}</span>
                    {{ $selectedTeam->name }}
                </h2>
                <a href="/toernooi#picker" class="text-sm text-green-600 hover:underline">← Ander land</a>
            </div>

            @foreach($squad as $group => $players)
                <div class="mb-4">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ $group }}</h3>
                    <div class="grid sm:grid-cols-2 gap-2">
                        @foreach($players as $player)
                            <form method="POST" action="/toernooi">
                                @csrf
                                <input type="hidden" name="top_scorer" value="{{ $player->name }}">
                                <button type="submit"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl border text-left transition-all
                                        {{ $myPrediction?->top_scorer === $player->name
                                            ? 'border-green-500 bg-green-50'
                                            : 'border-gray-100 hover:border-green-300 hover:bg-green-50' }}">
                                    <span class="text-sm font-medium text-gray-800 truncate">{{ $player->name }}</span>
                                    @if($myPrediction?->top_scorer === $player->name)
                                        <span class="text-green-600 text-xs flex-shrink-0">✅ gekozen</span>
                                    @else
                                        <span class="text-gray-300 text-xs flex-shrink-0">kies →</span>
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
            <h2 class="text-base font-semibold text-gray-700 mb-3">👥 Voorspellingen van iedereen</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                @foreach($allPredictions as $pred)
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 last:border-0
                        {{ $pred->user_id === auth()->id() ? 'bg-green-50' : '' }}">
                        <span class="flex-1 text-sm font-medium text-gray-800">
                            {{ $pred->user->name }}
                            @if($pred->user_id === auth()->id())
                                <span class="text-green-600 text-xs ml-1">(jij)</span>
                            @endif
                        </span>
                        <span class="text-sm text-gray-600">{{ $pred->top_scorer }}</span>
                        @if($tournamentResult?->top_scorer)
                            <span class="text-xs font-bold {{ $pred->points > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                {{ $pred->points > 0 ? "+{$pred->points}pt 🎉" : '0pt' }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
