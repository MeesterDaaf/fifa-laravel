@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold text-gray-800 mb-2">🏆 Toernooi Voorspelling</h1>
    <p class="text-gray-500 text-sm mb-6">Voorspel de grote toernooi-uitslagen. Hoe meer goed, hoe meer bonuspunten!</p>

    {{-- Deadline-status --}}
    @if(! $isOpen)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <p class="text-sm font-semibold text-red-800">🔒 De toernooivoorspelling is gesloten</p>
            <p class="text-xs text-red-700 mt-1">
                Het toernooi is begonnen{{ $deadline ? ' op '.format_day($deadline).' om '.to_nl_time($deadline)->format('H:i') : '' }}.
                Je voorspelling kan niet meer gewijzigd worden.
            </p>
        </div>
    @elseif($deadline)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
            <p class="text-sm font-semibold text-amber-800">⏳ Vul op tijd in!</p>
            <p class="text-xs text-amber-700 mt-1">
                Je kunt je toernooivoorspelling aanpassen tot de aftrap van de eerste wedstrijd:
                <strong>{{ format_day($deadline) }} om {{ to_nl_time($deadline)->format('H:i') }}</strong>. Daarna sluit het definitief.
            </p>
        </div>
    @endif

    {{-- Puntensysteem --}}
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
        <p class="font-semibold text-blue-800 text-sm mb-2">🎯 Toernooi punten</p>
        <ul class="text-xs text-blue-700 space-y-1">
            <li>🏆 Juiste toernooiwinnaar: <strong>{{ config('scoring.tournament.champion') }} punten</strong></li>
            <li>🥇 Juiste topscorer: <strong>{{ config('scoring.tournament.top_scorer') }} punten</strong></li>
            <li>🟨 Dichtst bij totaal gele kaarten: <strong>{{ config('scoring.tournament.yellow_cards') }} punten</strong></li>
            <li>🟥 Dichtst bij totaal rode kaarten: <strong>{{ config('scoring.tournament.red_cards') }} punten</strong></li>
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
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
            <p class="text-sm font-semibold text-yellow-800 mb-2">📣 Officiële uitslagen</p>
            <ul class="text-xs text-yellow-800 space-y-1">
                @if($tournamentResult->champion)
                    <li>🏆 Winnaar: <strong>{{ $tournamentResult->champion }}</strong></li>
                @endif
                @if($tournamentResult->top_scorer)
                    <li>🥇 Topscorer: <strong>{{ $tournamentResult->top_scorer }}</strong></li>
                @endif
                @if($tournamentResult->total_yellow_cards !== null)
                    <li>🟨 Gele kaarten: <strong>{{ $tournamentResult->total_yellow_cards }}</strong></li>
                @endif
                @if($tournamentResult->total_red_cards !== null)
                    <li>🟥 Rode kaarten: <strong>{{ $tournamentResult->total_red_cards }}</strong></li>
                @endif
            </ul>
            @if($myPrediction)
                <p class="text-xs text-yellow-700 mt-2 pt-2 border-t border-yellow-200">
                    Jouw toernooipunten: <strong>{{ $myPrediction->points }} punten</strong>
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
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="font-semibold text-gray-700 mb-3">Jouw voorspellingen</h2>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400">🏆 Winnaar</div>
                <div class="font-bold text-gray-800">{{ $myPrediction?->champion ?? '—' }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400">🥇 Topscorer</div>
                <div class="font-bold text-gray-800">{{ $myPrediction?->top_scorer ?? '—' }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400">🟨 Gele kaarten</div>
                <div class="font-bold text-gray-800">{{ $myPrediction?->total_yellow_cards ?? '—' }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400">🟥 Rode kaarten</div>
                <div class="font-bold text-gray-800">{{ $myPrediction?->total_red_cards ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Winnaar + kaarten formulier --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="font-semibold text-gray-700 mb-4">🏆 Winnaar &amp; kaarten</h2>
        <form method="POST" action="/toernooi" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Welk land wint het toernooi?</label>
                <select name="champion" @disabled(! $isOpen)
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 bg-white disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed">
                    <option value="">— Kies een land —</option>
                    @foreach($teams as $team)
                        @php $teamName = country_name($team->tla, $team->name); @endphp
                        <option value="{{ $teamName }}" {{ $myPrediction?->champion === $teamName ? 'selected' : '' }}>
                            {{ get_flag($team->tla) }} {{ $teamName }}
                        </option>
                    @endforeach
                </select>
                @if($teams->isEmpty())
                    <p class="text-xs text-gray-400 mt-1">Landenlijst nog niet geladen — vraag de admin om te synchroniseren.</p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">🟨 Totaal gele kaarten</label>
                    <input type="number" inputmode="numeric" name="total_yellow_cards" min="0" max="2000" @disabled(! $isOpen)
                        value="{{ old('total_yellow_cards', $myPrediction?->total_yellow_cards) }}"
                        placeholder="bv. 220"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">🟥 Totaal rode kaarten</label>
                    <input type="number" inputmode="numeric" name="total_red_cards" min="0" max="500" @disabled(! $isOpen)
                        value="{{ old('total_red_cards', $myPrediction?->total_red_cards) }}"
                        placeholder="bv. 12"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed">
                </div>
            </div>

            <button type="submit" @disabled(! $isOpen)
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed">
                {{ $isOpen ? '💾 Opslaan' : '🔒 Gesloten' }}
            </button>
        </form>
    </div>

    {{-- Topscorer picker --}}
    <h2 class="font-semibold text-gray-700 mb-3">🥇 Kies je topscorer</h2>
    <div id="picker" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8 scroll-mt-20">
        @if($teams->isEmpty())
            <div class="text-center text-gray-500 text-sm py-4">
                <div class="text-3xl mb-2">📭</div>
                Nog geen spelers geladen. Vraag de admin om <strong>teams &amp; spelers te synchroniseren</strong>.
            </div>
        @elseif(! $selectedTeam)
            {{-- Stap 1: kies een land --}}
            <p class="text-sm text-gray-500 mb-4">Klik een land aan om de selectie te zien.</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($teams as $team)
                    <a href="/toernooi?team={{ $team->tla }}#picker"
                        class="flex items-center gap-2 px-3 py-2.5 rounded-xl border border-gray-100 hover:border-green-300 hover:bg-green-50 transition-all">
                        <span class="text-xl">{{ get_flag($team->tla) }}</span>
                        <span class="text-sm font-medium text-gray-800 truncate">{{ country_name($team->tla, $team->name) }}</span>
                    </a>
                @endforeach
            </div>
        @else
            {{-- Stap 2: kies een speler --}}
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                    <span class="text-2xl">{{ get_flag($selectedTeam->tla) }}</span>
                    {{ country_name($selectedTeam->tla, $selectedTeam->name) }}
                </h3>
                <a href="/toernooi#picker" class="text-sm text-green-600 hover:underline">← Ander land</a>
            </div>

            @foreach($squad as $group => $players)
                <div class="mb-4">
                    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ $group }}</h4>
                    <div class="grid sm:grid-cols-2 gap-2">
                        @foreach($players as $player)
                            <form method="POST" action="/toernooi">
                                @csrf
                                <input type="hidden" name="top_scorer" value="{{ $player->name }}">
                                <button type="submit" @disabled(! $isOpen)
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl border text-left transition-all disabled:opacity-60 disabled:cursor-not-allowed
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
                    <div class="px-4 py-3 border-b border-gray-50 last:border-0
                        {{ $pred->user_id === auth()->id() ? 'bg-green-50' : '' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-800">
                                {{ $pred->user->name }}
                                @if($pred->user_id === auth()->id())
                                    <span class="text-green-600 text-xs ml-1">(jij)</span>
                                @endif
                            </span>
                            @if($hasResult)
                                <span class="text-xs font-bold {{ $pred->points > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $pred->points > 0 ? "+{$pred->points}pt 🎉" : '0pt' }}
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 mt-1 flex flex-wrap gap-x-3 gap-y-0.5">
                            <span class="{{ $pred->points_champion > 0 ? 'text-green-600 font-semibold' : '' }}">
                                🏆 {{ $pred->champion ?? '—' }}@if($pred->points_champion > 0) +{{ $pred->points_champion }}@endif
                            </span>
                            <span class="{{ $pred->points_top_scorer > 0 ? 'text-green-600 font-semibold' : '' }}">
                                🥇 {{ $pred->top_scorer ?? '—' }}@if($pred->points_top_scorer > 0) +{{ $pred->points_top_scorer }}@endif
                            </span>
                            <span class="{{ $pred->points_yellow > 0 ? 'text-green-600 font-semibold' : '' }}">
                                🟨 {{ $pred->total_yellow_cards ?? '—' }}@if($pred->points_yellow > 0) 🎯+{{ $pred->points_yellow }}@endif
                            </span>
                            <span class="{{ $pred->points_red > 0 ? 'text-green-600 font-semibold' : '' }}">
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
