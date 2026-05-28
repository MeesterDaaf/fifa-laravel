@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold text-gray-800 mb-6">⚽ Wedstrijden &amp; Voorspellingen</h1>

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
                        <div class="flex items-center gap-3">
                            <div class="flex-1 flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-800 min-w-[60px]">
                                    {{ get_flag($match->home_team_code) }} {{ $match->home_team_code }}
                                </span>

                                @if($isPast)
                                    <span class="bg-gray-800 text-white text-sm font-bold px-3 py-1 rounded-lg">
                                        {{ $match->home_score }} - {{ $match->away_score }}
                                    </span>
                                @else
                                    <span class="bg-gray-100 text-gray-500 text-xs font-medium px-3 py-1 rounded-lg">vs</span>
                                @endif

                                <span class="text-sm font-semibold text-gray-800 min-w-[60px] text-right">
                                    {{ $match->away_team_code }} {{ get_flag($match->away_team_code) }}
                                </span>
                            </div>

                            <div class="text-right flex-shrink-0">
                                <div class="text-xs text-gray-400">{{ format_date($match->scheduled_at) }}</div>
                                @if($pred)
                                    <span class="text-xs text-green-600 font-medium">✅ {{ $pred->home_score }}-{{ $pred->away_score }}</span>
                                @elseif($isOpen)
                                    <span class="text-xs text-orange-500 font-medium">⏳ Voorspel</span>
                                @else
                                    <span class="text-xs text-gray-400">Gesloten</span>
                                @endif
                            </div>
                        </div>

                        @if($match->match_group)
                            <div class="mt-1 text-xs text-gray-400">Groep {{ $match->match_group }}</div>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach

</div>
@endsection
