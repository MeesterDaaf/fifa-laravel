@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 stagger">

    <p class="kicker mb-1">Nu bezig</p>
    <h1 class="h-display text-4xl mb-2">Li<span class="text-volt-400">ve</span></h1>

    @if($liveBlocks->isEmpty())
        <div class="card p-8 text-center mt-4">
            <div class="text-4xl mb-3">📺</div>
            <p class="text-white/60 mb-1">Er is nu geen wedstrijd bezig.</p>
            @if($nextFixture)
                <p class="text-sm text-white/40">
                    Volgende wedstrijd: {{ get_flag($nextFixture->home_team_code) }} {{ country_name($nextFixture->home_team_code, $nextFixture->home_team) }}
                    – {{ country_name($nextFixture->away_team_code, $nextFixture->away_team) }} {{ get_flag($nextFixture->away_team_code) }}
                    op {{ $nextFixture->scheduled_at->translatedFormat('l j F, H:i') }}
                </p>
            @endif
        </div>
    @else
        <p class="text-white/55 text-sm mb-6">
            De virtuele stand: alsof de huidige tussenstand{{ $liveBlocks->count() > 1 ? 'en' : '' }} de eindstand zou zijn.
        </p>

        {{-- Live wedstrijden met voorspellingen --}}
        @foreach($liveBlocks as $block)
            @php $match = $block['fixture']; @endphp
            <section class="mb-6">
                <div class="card overflow-hidden">
                    <div class="px-4 py-4 bg-white/3 border-b border-white/8">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-display font-bold uppercase tracking-wider text-signal-red">
                                <span class="w-1.5 h-1.5 rounded-full bg-signal-red animate-pulse"></span> Live
                            </span>
                            <span class="text-[10px] font-display font-bold uppercase tracking-wider text-white/40">{{ $match->stageLabel() }}</span>
                        </div>
                        <div class="flex items-center justify-center gap-3">
                            <span class="flex-1 flex items-center justify-end gap-1.5 min-w-0 text-sm font-semibold text-white">
                                <span class="truncate text-right">{{ country_name($match->home_team_code, $match->home_team) }}</span>
                                <span class="shrink-0">{{ get_flag($match->home_team_code) }}</span>
                            </span>
                            <span class="scorebox text-lg shrink-0">{{ $match->home_score ?? 0 }}-{{ $match->away_score ?? 0 }}</span>
                            <span class="flex-1 flex items-center gap-1.5 min-w-0 text-sm font-semibold text-white">
                                <span class="shrink-0">{{ get_flag($match->away_team_code) }}</span>
                                <span class="truncate">{{ country_name($match->away_team_code, $match->away_team) }}</span>
                            </span>
                        </div>
                        @if($match->first_goal_minute)
                            <p class="text-center text-xs text-white/40 mt-1">⚽ 1e doelpunt: minuut {{ $match->first_goal_minute }}</p>
                        @endif
                    </div>

                    {{-- Voorspellingen van de deelnemers voor deze wedstrijd --}}
                    @forelse($block['predictions'] as $pred)
                        @php $pts = $block['points'][$pred->user_id] ?? 0; @endphp
                        <div class="row {{ $pred->user_id === auth()->id() ? 'row-me' : '' }}">
                            <div class="flex-1 min-w-0">
                                <span class="font-medium text-white/90 text-sm truncate block">
                                    {{ $pred->user->name }}
                                    @if($pred->user_id === auth()->id())
                                        <span class="text-volt-400 text-xs ml-1">(jij)</span>
                                    @endif
                                </span>
                                @if($pred->first_goal_minute !== null)
                                    <span class="text-xs text-white/40">⚽ 1e doelpunt: minuut {{ $pred->first_goal_minute }}</span>
                                @endif
                            </div>
                            <span class="text-sm scoreline text-white/70 shrink-0">{{ $pred->home_score }}-{{ $pred->away_score }}</span>
                            <span class="w-14 text-right scoreline shrink-0 {{ $pts > 0 ? 'text-volt-400' : 'text-white/30' }}">+{{ $pts }}pt</span>
                        </div>
                    @empty
                        <p class="px-4 py-3 text-sm text-white/40">Niemand heeft deze wedstrijd voorspeld.</p>
                    @endforelse
                </div>
            </section>
        @endforeach

        {{-- Virtuele ranglijst --}}
        <h2 class="font-display font-bold uppercase tracking-wide text-white mb-3">📊 Virtuele stand</h2>
        <div class="card overflow-hidden mb-6">
            <div class="flex items-center gap-3 px-4 py-2 bg-white/3 border-b border-white/8 text-xs font-display font-bold text-white/40 uppercase tracking-wider">
                <span class="w-7">#</span>
                <span class="flex-1">Naam</span>
                <span class="w-14 text-right">Live</span>
                <span class="w-16 text-right text-white/70">Totaal</span>
            </div>
            @foreach($virtualStandings as $i => $entry)
                <div class="row {{ $entry['id'] === auth()->id() ? 'row-me' : '' }}">
                    <div class="w-7 shrink-0">
                        @if($i < 3)
                            <span class="rank rank-{{ $i + 1 }}">{{ $i + 1 }}</span>
                        @else
                            <span class="text-sm text-white/45 scoreline pl-1.5">{{ $i + 1 }}</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-white/90 text-sm truncate block">
                            {{ $entry['name'] }}
                            @if($entry['id'] === auth()->id())
                                <span class="text-volt-400 text-xs ml-1">(jij)</span>
                            @endif
                        </span>
                        @if($entry['delta'] !== 0)
                            <span class="text-xs font-semibold {{ $entry['delta'] > 0 ? 'text-volt-400' : 'text-signal-red' }}">
                                {{ $entry['delta'] > 0 ? '▲' : '▼' }}{{ abs($entry['delta']) }} t.o.v. huidige stand
                            </span>
                        @endif
                    </div>
                    <span class="w-14 text-right text-sm scoreline {{ $entry['virtualGain'] > 0 ? 'text-volt-400' : 'text-white/30' }}">+{{ $entry['virtualGain'] }}</span>
                    <span class="w-16 text-right scoreline text-lg {{ $i === 0 ? 'text-gold-400' : 'text-volt-400' }}">{{ $entry['virtualTotal'] }}pt</span>
                </div>
            @endforeach
        </div>

        <p class="text-xs text-white/35 text-center">Deze pagina ververst elke 60 seconden automatisch.</p>
        <script>setTimeout(() => location.reload(), 60000);</script>
    @endif

</div>
@endsection
