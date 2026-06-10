@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 space-y-8 stagger">

    @if(($awaitingResults ?? 0) > 0)
        <a href="/admin" class="block card card-hover p-4 border-signal-amber/40!">
            <span class="font-semibold text-signal-amber">⚠️ {{ $awaitingResults }} gespeelde wedstrijd{{ $awaitingResults !== 1 ? 'en' : '' }} wacht{{ $awaitingResults === 1 ? '' : 'en' }} op invoer</span>
            <span class="block text-sm text-white/60">Ga naar het admin-paneel om de uitslagen in te voeren →</span>
        </a>
    @endif

    {{-- Voortgangsberekening (gebruikt in het blok hiernaast) --}}
    @php
        $matchesDone = $openCount > 0 && $predictedCount >= $openCount;
        $tournamentComplete = $tournamentDone === 4;
        $allDone = ($openCount === 0 || $matchesDone) && $tournamentComplete;
    @endphp

    {{-- Hero + "maak je voorspellingen compleet" naast elkaar (2 kolommen) --}}
    <div class="grid md:grid-cols-2 gap-6 items-start">

        {{-- Hero --}}
        <div class="card-volt p-6 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-10 text-[9rem] leading-none opacity-[0.07] select-none" aria-hidden="true">⚽</div>
            <p class="kicker mb-1">Matchday · Wereldkampioenschap 2026</p>
            <h1 class="h-display text-4xl">Welkom, <span class="text-volt-400">{{ auth()->user()->name }}</span></h1>
            <div class="mt-5 flex flex-wrap gap-3 relative">
                <a href="/voorspellingen" class="btn btn-volt">⚽ Maak voorspelling</a>
                <a href="/toernooi" class="btn btn-outline">🏆 Toernooi</a>
                @if($whatsappGroupUrl)
                    <a href="{{ $whatsappGroupUrl }}" target="_blank" rel="noopener" class="btn btn-wa">💬 WhatsApp</a>
                @endif
            </div>
        </div>

        {{-- Voortgang: maak duidelijk dat je BEIDE moet doen --}}
        <div class="card p-5 {{ $allDone ? 'border-volt-500/30!' : 'border-signal-amber/30!' }}">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xl">{{ $allDone ? '✅' : '📋' }}</span>
                <h2 class="font-display font-bold uppercase tracking-wide text-white">
                    {{ $allDone ? 'Je bent helemaal bij — alles voorspeld!' : 'Maak je voorspellingen compleet' }}
                </h2>
            </div>

            @unless($allDone)
                <p class="text-sm text-white/55 mb-4">
                    Je doet mee op twee manieren: voorspel de <strong class="text-white/85">wedstrijden</strong> én maak je <strong class="text-white/85">toernooi-voorspellingen</strong>. Vergeet geen van beide!
                </p>
            @endunless

            <div class="grid sm:grid-cols-2 gap-3">

                {{-- Wedstrijden --}}
                <a href="/voorspellingen" class="block card card-hover p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-display font-bold uppercase tracking-wide text-sm text-white">⚽ Wedstrijden</span>
                        @if($openCount === 0)
                            <span class="pill pill-muted">geen open</span>
                        @elseif($matchesDone)
                            <span class="pill pill-ok">✓ compleet</span>
                        @else
                            <span class="pill pill-wait">{{ $openCount - $predictedCount }} te doen</span>
                        @endif
                    </div>
                    @if($openCount > 0)
                        <div class="meter mb-1.5">
                            <div class="meter-fill" style="width: {{ round($predictedCount / $openCount * 100) }}%"></div>
                        </div>
                        <p class="text-xs text-white/45">{{ $predictedCount }} van {{ $openCount }} open wedstrijden voorspeld</p>
                    @else
                        <p class="text-xs text-white/45">Er staan nu geen wedstrijden open om te voorspellen.</p>
                    @endif
                </a>

                {{-- Toernooi --}}
                <a href="/toernooi" class="block card card-hover p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-display font-bold uppercase tracking-wide text-sm text-white">🏆 Toernooi</span>
                        @if($tournamentComplete)
                            <span class="pill pill-ok">✓ compleet</span>
                        @else
                            <span class="pill pill-wait">{{ $tournamentDone }}/4 gedaan</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach([
                            'champion' => 'Winnaar',
                            'top_scorer' => 'Topscorer',
                            'yellow' => 'Gele kaarten',
                            'red' => 'Rode kaarten',
                        ] as $key => $label)
                            <span class="pill {{ $tournamentStatus[$key] ? 'pill-ok' : 'pill-muted' }}">
                                {{ $tournamentStatus[$key] ? '✓' : '○' }} {{ $label }}
                            </span>
                        @endforeach
                    </div>
                </a>

            </div>

            @if($openCount - $predictedCount > 0)
                <form method="POST" action="/voorspellingen/auto-fill" class="mt-3"
                    onsubmit="return confirm('De nog-open wedstrijden die je nog niet hebt voorspeld worden automatisch ingevuld op basis van de kansberekening. Je kunt ze daarna nog aanpassen. Doorgaan?');">
                    @csrf
                    <button type="submit" class="btn btn-volt w-full">
                        ⚡ Vul mijn open wedstrijden automatisch in
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">

        {{-- Vraag van de dag (grappige trivia) --}}
        @include('partials.trivia')

        {{-- Aankomende wedstrijden --}}
        <section>
            <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-3">📅 Aankomende wedstrijden</h2>
            @if($upcoming->isEmpty())
                <p class="text-white/50 text-sm card p-4">
                    Geen geplande wedstrijden. Sync eerst wedstrijden via admin.
                </p>
            @else
                <div class="space-y-2">
                    @foreach($upcoming as $match)
                        <a href="/voorspellingen/{{ $match->id }}" class="block card card-hover p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <span class="flex items-center gap-1 min-w-0 text-sm font-semibold text-white">
                                        <span class="shrink-0">{{ get_flag($match->home_team_code) }}</span>
                                        <span class="scoreline">{{ $match->home_team_code }}</span>
                                    </span>
                                    <span class="text-white/30 text-xs font-bold shrink-0">vs</span>
                                    <span class="flex items-center justify-end gap-1 min-w-0 text-sm font-semibold text-white">
                                        <span class="scoreline">{{ $match->away_team_code }}</span>
                                        <span class="shrink-0">{{ get_flag($match->away_team_code) }}</span>
                                    </span>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-xs text-white/45">{{ format_date_short($match->scheduled_at) }}</div>
                                    @if(isset($myPredIds[$match->id]))
                                        <span class="text-xs text-volt-400 font-medium">✓ Voorspeld</span>
                                    @else
                                        <span class="text-xs text-signal-amber font-medium">⏳ Voorspel nog</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                    <a href="/voorspellingen" class="block text-center text-sm text-volt-400 hover:text-volt-300 py-1">
                        Alle wedstrijden →
                    </a>
                </div>
            @endif
        </section>

        {{-- Top 5 Ranglijst --}}
        <section>
            <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-3">🏆 Top 5 Ranglijst</h2>
            @if(empty($leaderboard))
                <p class="text-white/50 text-sm card p-4">Nog geen punten gescoord</p>
            @else
                <div class="card overflow-hidden">
                    @foreach($leaderboard as $i => $entry)
                        <div class="row {{ $entry['id'] === auth()->id() ? 'row-me' : '' }}">
                            <span class="rank {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : '')) }}">
                                {{ $i + 1 }}
                            </span>
                            <span class="flex-1 font-medium text-sm text-white truncate">
                                {{ $entry['name'] }}
                                @if($entry['id'] === auth()->id())
                                    <span class="text-volt-400 text-xs ml-1">(jij)</span>
                                @endif
                            </span>
                            <span class="scoreline text-lg {{ $i === 0 ? 'text-gold-400' : 'text-volt-400' }} flex-shrink-0">{{ $entry['totalPoints'] }}<span class="text-xs text-white/40 ml-0.5">pt</span></span>
                        </div>
                    @endforeach
                    <a href="/ranglijst" class="block text-center text-sm text-volt-400 hover:text-volt-300 hover:bg-white/5 py-3 transition-colors">
                        Volledige ranglijst →
                    </a>
                </div>
            @endif
        </section>

    </div>

    {{-- Recente uitslagen --}}
    @if($recent->isNotEmpty())
        <section>
            <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-3">🎯 Recente uitslagen</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($recent as $match)
                    <div class="card p-4">
                        <div class="text-xs text-white/40 mb-2">{{ format_date_short($match->scheduled_at) }}</div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-medium text-white/85">
                                {{ get_flag($match->home_team_code) }} <span class="scoreline">{{ $match->home_team_code }}</span>
                            </span>
                            <span class="scorebox text-lg">
                                {{ $match->home_score ?? '?' }} - {{ $match->away_score ?? '?' }}
                            </span>
                            <span class="text-sm font-medium text-white/85">
                                <span class="scoreline">{{ $match->away_team_code }}</span> {{ get_flag($match->away_team_code) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
