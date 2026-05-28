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
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <h2 class="font-semibold text-gray-700 mb-4">
            {{ $myPrediction ? 'Jouw voorspelling aanpassen' : 'Maak jouw voorspelling' }}
        </h2>

        <form method="POST" action="/toernooi">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Naam van de topscorer</label>
                <input
                    type="text"
                    name="top_scorer"
                    value="{{ old('top_scorer', $myPrediction?->top_scorer) }}"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500"
                    placeholder="bv. Kylian Mbappé"
                >
                @error('top_scorer')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition-colors">
                🥇 Voorspelling opslaan
            </button>
        </form>
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
