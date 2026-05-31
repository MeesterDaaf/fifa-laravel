@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold text-gray-800 mb-4">⚽ Wedstrijden &amp; Voorspellingen</h1>

    @if(($openUnpredicted ?? 0) > 0)
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6">
            <p class="text-sm text-green-800 mb-3">
                Geen tijd om alles los in te vullen? Laat de app je <strong>{{ $openUnpredicted }}</strong> nog-open
                wedstrijd{{ $openUnpredicted !== 1 ? 'en' : '' }} realistisch invullen op basis van de kansberekening — daarna kun je alles nog aanpassen.
            </p>
            <form method="POST" action="/voorspellingen/auto-fill"
                onsubmit="return confirm('De {{ $openUnpredicted }} nog-open wedstrijden die je nog niet hebt voorspeld worden automatisch ingevuld. Je kunt ze daarna nog aanpassen. Doorgaan?');">
                @csrf
                <button type="submit"
                    class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">
                    ⚡ Vul mijn open wedstrijden automatisch in
                </button>
            </form>
        </div>
    @endif

    @if($byStage->isEmpty())
        <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
            <div class="text-4xl mb-3">📭</div>
            <p class="text-gray-500">Nog geen wedstrijden geladen.</p>
            <p class="text-gray-400 text-sm mt-1">Vraag de admin om wedstrijden te synchroniseren.</p>
        </div>
    @endif

    @php
        $stageOrder = ['GROUP_STAGE', 'LAST_32', 'LAST_16', 'QUARTER_FINALS', 'SEMI_FINALS', 'THIRD_PLACE', 'FINAL'];
        $sorted = $byStage->sortBy(fn($_, $k) => array_search($k, $stageOrder) !== false ? array_search($k, $stageOrder) : 99);
    @endphp

    @foreach($sorted as $stage => $matches)
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-500 uppercase tracking-wider mb-3">
                {{ $matches->first()->stageLabel() }}
            </h2>
            <div class="space-y-2">
                @foreach($matches as $match)
                    @php
                        $pred = $myPredictions->get($match->id);
                        $isOpen = $match->isOpen();
                        $isPast = $match->isFinished();
                    @endphp
                    <a href="/voorspellingen/{{ $match->id }}"
                        class="block bg-white rounded-xl px-4 py-3 shadow-sm border transition-all
                            {{ $isOpen ? 'border-gray-100 hover:border-green-300 hover:shadow-md' : 'border-gray-100 opacity-80' }}">
                        {{-- Regel 1: teams op de volledige breedte --}}
                        <div class="flex items-center gap-3">
                            <span class="flex-1 flex items-center gap-2 min-w-0 text-sm font-semibold text-gray-800">
                                <span class="shrink-0 text-base">{{ get_flag($match->home_team_code) }}</span>
                                <span class="truncate">{{ country_name($match->home_team_code, $match->home_team) }}</span>
                            </span>

                            @if($isPast)
                                <span class="shrink-0 bg-gray-800 text-white text-sm font-bold px-3 py-1 rounded-lg">
                                    {{ $match->home_score }} - {{ $match->away_score }}
                                </span>
                            @else
                                <span class="shrink-0 bg-gray-100 text-gray-500 text-xs font-medium px-2.5 py-1 rounded-lg">vs</span>
                            @endif

                            <span class="flex-1 flex items-center justify-end gap-2 min-w-0 text-sm font-semibold text-gray-800">
                                <span class="truncate text-right">{{ country_name($match->away_team_code, $match->away_team) }}</span>
                                <span class="shrink-0 text-base">{{ get_flag($match->away_team_code) }}</span>
                            </span>
                        </div>

                        {{-- Regel 2: datum/groep links, voorspelstatus rechts --}}
                        <div class="flex items-center justify-between gap-2 mt-2 text-xs">
                            <span class="text-gray-400 truncate">
                                {{ format_date($match->scheduled_at) }}@if($match->match_group) · {{ group_label($match->match_group) }}@endif
                            </span>
                            @if($pred)
                                <span class="shrink-0 text-green-600 font-medium">✅ {{ $pred->home_score }}-{{ $pred->away_score }}</span>
                            @elseif($isOpen)
                                <span class="shrink-0 text-orange-500 font-medium">⏳ Voorspel</span>
                            @else
                                <span class="shrink-0 text-gray-400">Gesloten</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach

</div>
@endsection
