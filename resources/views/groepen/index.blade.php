@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 stagger">

    <p class="kicker mb-1">Stand van zaken</p>
    <h1 class="h-display text-4xl mb-2">Groeps<span class="text-volt-400">fase</span></h1>
    <p class="text-white/55 text-sm mb-6">
        De stand wordt automatisch berekend uit de ingevoerde uitslagen (3 punten winst, 1 gelijk).
    </p>

    @if(empty($overview))
        <div class="card p-8 text-center">
            <div class="text-4xl mb-3">📭</div>
            <p class="text-white/60">Nog geen groepswedstrijden geladen.</p>
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-5">
        @foreach($overview as $key => $data)
            <section class="card overflow-hidden">
                <h2 class="flex items-center justify-between px-4 py-3 border-b border-white/8 bg-white/3">
                    <span class="font-display font-bold uppercase tracking-wide text-white">{{ group_label($key) }}</span>
                    <span class="pill {{ $data['complete'] ? 'pill-ok' : 'pill-muted' }}">
                        {{ $data['complete'] ? '✓ afgerond' : 'nog bezig' }}
                    </span>
                </h2>

                {{-- Stand --}}
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[11px] text-white/35 uppercase tracking-wider font-display">
                            <th class="text-left font-semibold pl-4 py-2 w-6">#</th>
                            <th class="text-left font-semibold py-2">Team</th>
                            <th class="text-center font-semibold py-2 w-8" title="Gespeeld">G</th>
                            <th class="text-center font-semibold py-2 w-10" title="Doelsaldo">DS</th>
                            <th class="text-center font-semibold py-2 w-10 pr-4" title="Punten">Ptn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['standing'] as $i => $row)
                            @php
                                $gd = $row['gf'] - $row['ga'];
                                $status = $qualified[$row['code']] ?? null;
                                $rowClass = match ($status) {
                                    'direct' => 'bg-volt-500/8',
                                    'third'  => 'bg-signal-blue/8',
                                    default  => (! $data['complete'] && $i < 2) ? 'bg-volt-500/4' : '',
                                };
                            @endphp
                            <tr class="border-t border-white/5 {{ $rowClass }}">
                                <td class="pl-4 py-2 text-white/35 scoreline">{{ $i + 1 }}</td>
                                <td class="py-2">
                                    <span class="font-medium text-white/90">{{ get_flag($row['code']) }} {{ country_name($row['code'], $row['name']) }}</span>
                                    @if($status === 'direct')
                                        <span class="ml-1 text-[10px] font-semibold text-volt-400">✓ door</span>
                                    @elseif($status === 'third')
                                        <span class="ml-1 text-[10px] font-semibold text-signal-blue">✓ beste 3e</span>
                                    @endif
                                </td>
                                <td class="text-center py-2 text-white/45 scoreline">{{ $row['played'] }}</td>
                                <td class="text-center py-2 text-white/45 scoreline">{{ $gd > 0 ? '+'.$gd : $gd }}</td>
                                <td class="text-center py-2 pr-4 scoreline text-volt-400">{{ $row['points'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Wedstrijden --}}
                <div class="border-t border-white/8">
                    @foreach($data['matches'] as $m)
                        <a href="/voorspellingen/{{ $m->id }}" class="flex items-center gap-2 px-4 py-2 hover:bg-white/4 transition-colors text-xs">
                            <span class="flex-1 flex items-center justify-end gap-1 min-w-0 text-white/70">
                                <span class="truncate text-right">{{ country_name($m->home_team_code, $m->home_team) }}</span>
                                <span class="shrink-0">{{ get_flag($m->home_team_code) }}</span>
                            </span>
                            @if($m->isFinished())
                                <span class="shrink-0 scorebox text-xs">{{ $m->home_score }}-{{ $m->away_score }}</span>
                            @else
                                <span class="shrink-0 text-white/35 scoreline">{{ to_nl_time($m->scheduled_at)->format('d/m H:i') }}</span>
                            @endif
                            <span class="flex-1 flex items-center gap-1 min-w-0 text-white/70">
                                <span class="shrink-0">{{ get_flag($m->away_team_code) }}</span>
                                <span class="truncate">{{ country_name($m->away_team_code, $m->away_team) }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    {{-- Legenda --}}
    <div class="mt-6 flex flex-wrap gap-4 text-xs text-white/50">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-volt-500/30 border border-volt-500/50 inline-block"></span> Top 2 — direct door</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-signal-blue/30 border border-signal-blue/50 inline-block"></span> Beste nr. 3 (8 plekken, bekend zodra alle groepen klaar zijn)</span>
    </div>
    <p class="mt-2 text-xs text-white/35">
        Volgorde volgens FIFA-regels: punten → doelsaldo → doelpunten → onderling resultaat.
    </p>

</div>
@endsection
