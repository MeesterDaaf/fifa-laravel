@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold text-gray-800 mb-2">📊 Groepsfase</h1>
    <p class="text-gray-500 text-sm mb-6">
        De stand wordt automatisch berekend uit de ingevoerde uitslagen (3 punten winst, 1 gelijk).
    </p>

    @if(empty($overview))
        <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
            <div class="text-4xl mb-3">📭</div>
            <p class="text-gray-500">Nog geen groepswedstrijden geladen.</p>
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-5">
        @foreach($overview as $key => $data)
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <h2 class="flex items-center justify-between font-bold text-gray-800 px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <span>{{ group_label($key) }}</span>
                    <span class="text-xs font-medium {{ $data['complete'] ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $data['complete'] ? '✓ afgerond' : 'nog bezig' }}
                    </span>
                </h2>

                {{-- Stand --}}
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[11px] text-gray-400 uppercase tracking-wider">
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
                                    'direct' => 'bg-green-50',
                                    'third'  => 'bg-blue-50',
                                    default  => (! $data['complete'] && $i < 2) ? 'bg-green-50/30' : '',
                                };
                            @endphp
                            <tr class="border-t border-gray-50 {{ $rowClass }}">
                                <td class="pl-4 py-2 text-gray-400">{{ $i + 1 }}</td>
                                <td class="py-2">
                                    <span class="font-medium text-gray-800">{{ get_flag($row['code']) }} {{ country_name($row['code'], $row['name']) }}</span>
                                    @if($status === 'direct')
                                        <span class="ml-1 text-[10px] font-semibold text-green-700">✓ door</span>
                                    @elseif($status === 'third')
                                        <span class="ml-1 text-[10px] font-semibold text-blue-700">✓ beste 3e</span>
                                    @endif
                                </td>
                                <td class="text-center py-2 text-gray-500">{{ $row['played'] }}</td>
                                <td class="text-center py-2 text-gray-500">{{ $gd > 0 ? '+'.$gd : $gd }}</td>
                                <td class="text-center py-2 pr-4 font-bold text-green-700">{{ $row['points'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Wedstrijden --}}
                <div class="border-t border-gray-100">
                    @foreach($data['matches'] as $m)
                        <a href="/voorspellingen/{{ $m->id }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 transition-colors text-xs">
                            <span class="flex-1 flex items-center justify-end gap-1 min-w-0 text-gray-700">
                                <span class="truncate text-right">{{ country_name($m->home_team_code, $m->home_team) }}</span>
                                <span class="shrink-0">{{ get_flag($m->home_team_code) }}</span>
                            </span>
                            @if($m->isFinished())
                                <span class="shrink-0 font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded">{{ $m->home_score }}-{{ $m->away_score }}</span>
                            @else
                                <span class="shrink-0 text-gray-400">{{ to_nl_time($m->scheduled_at)->format('d/m H:i') }}</span>
                            @endif
                            <span class="flex-1 flex items-center gap-1 min-w-0 text-gray-700">
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
    <div class="mt-6 flex flex-wrap gap-4 text-xs text-gray-500">
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-100 inline-block"></span> Top 2 — direct door</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-100 inline-block"></span> Beste nr. 3 (8 plekken, bekend zodra alle groepen klaar zijn)</span>
    </div>
    <p class="mt-2 text-xs text-gray-400">
        Volgorde volgens FIFA-regels: punten → doelsaldo → doelpunten → onderling resultaat.
    </p>

</div>
@endsection
