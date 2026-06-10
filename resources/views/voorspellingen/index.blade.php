@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 stagger">

    <p class="kicker mb-1">Speelschema</p>
    <h1 class="h-display text-4xl mb-6">Wedstrijden &amp; <span class="text-volt-400">voorspellingen</span></h1>

    @if(($openUnpredicted ?? 0) > 0)
        <div class="card-volt p-4 mb-6">
            <p class="text-sm text-white/75 mb-3">
                Geen tijd om alles los in te vullen? Laat de app je <strong class="text-volt-300">{{ $openUnpredicted }}</strong> nog-open
                wedstrijd{{ $openUnpredicted !== 1 ? 'en' : '' }} realistisch invullen op basis van de kansberekening — daarna kun je alles nog aanpassen.
            </p>
            <form method="POST" action="/voorspellingen/auto-fill"
                onsubmit="return confirm('De {{ $openUnpredicted }} nog-open wedstrijden die je nog niet hebt voorspeld worden automatisch ingevuld. Je kunt ze daarna nog aanpassen. Doorgaan?');">
                @csrf
                <button type="submit" class="btn btn-volt w-full sm:w-auto">
                    ⚡ Vul mijn open wedstrijden automatisch in
                </button>
            </form>
        </div>
    @endif

    @if($byStage->isEmpty())
        <div class="card p-8 text-center">
            <div class="text-4xl mb-3">📭</div>
            <p class="text-white/60">Nog geen wedstrijden geladen.</p>
            <p class="text-white/40 text-sm mt-1">Vraag de admin om wedstrijden te synchroniseren.</p>
        </div>
    @endif

    @php
        $stageOrder = ['GROUP_STAGE', 'LAST_32', 'LAST_16', 'QUARTER_FINALS', 'SEMI_FINALS', 'THIRD_PLACE', 'FINAL'];
        $sorted = $byStage->sortBy(fn($_, $k) => array_search($k, $stageOrder) !== false ? array_search($k, $stageOrder) : 99);
    @endphp

    @foreach($sorted as $stage => $matches)
        <section class="mb-8">
            <h2 class="flex items-center gap-3 mb-3">
                <span class="font-display font-bold uppercase tracking-[0.15em] text-sm text-volt-500">{{ $matches->first()->stageLabel() }}</span>
                <span class="flex-1 h-px bg-gradient-to-r from-volt-500/30 to-transparent"></span>
            </h2>
            <div class="space-y-2">
                @foreach($matches as $match)
                    @php
                        $pred = $myPredictions->get($match->id);
                        $isOpen = $match->isOpen();
                        $isPast = $match->isFinished();
                    @endphp
                    <a href="/voorspellingen/{{ $match->id }}"
                        class="block card px-4 py-3 {{ $isOpen ? 'card-hover' : 'opacity-70' }}">
                        {{-- Regel 1: teams op de volledige breedte --}}
                        <div class="flex items-center gap-3">
                            <span class="flex-1 flex items-center gap-2 min-w-0 text-sm font-semibold text-white">
                                <span class="shrink-0 text-base">{{ get_flag($match->home_team_code) }}</span>
                                <span class="truncate">{{ country_name($match->home_team_code, $match->home_team) }}</span>
                            </span>

                            @if($isPast)
                                <span class="shrink-0 scorebox text-sm">
                                    {{ $match->home_score }} - {{ $match->away_score }}
                                </span>
                            @else
                                <span class="shrink-0 scorebox-muted">vs</span>
                            @endif

                            <span class="flex-1 flex items-center justify-end gap-2 min-w-0 text-sm font-semibold text-white">
                                <span class="truncate text-right">{{ country_name($match->away_team_code, $match->away_team) }}</span>
                                <span class="shrink-0 text-base">{{ get_flag($match->away_team_code) }}</span>
                            </span>
                        </div>

                        {{-- Regel 2: datum/groep links, voorspelstatus rechts --}}
                        <div class="flex items-center justify-between gap-2 mt-2 text-xs">
                            <span class="text-white/40 truncate">
                                {{ format_date($match->scheduled_at) }}@if($match->match_group) · {{ group_label($match->match_group) }}@endif
                            </span>
                            @if($pred)
                                <span class="shrink-0 text-volt-400 font-semibold scoreline">✓ {{ $pred->home_score }}-{{ $pred->away_score }}</span>
                            @elseif($isOpen)
                                <span class="shrink-0 text-signal-amber font-medium">⏳ Voorspel</span>
                            @else
                                <span class="shrink-0 text-white/35">Gesloten</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach

</div>
@endsection
