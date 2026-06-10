@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 stagger">

    <p class="kicker mb-1">De selectie</p>
    <h1 class="h-display text-4xl mb-2">Deel<span class="text-volt-400">nemers</span></h1>
    <p class="text-white/55 text-sm mb-6">
        Bekijk de voorspellingen van alle deelnemers. Klik op een naam voor het volledige overzicht.
    </p>

    @if(empty($participants))
        <div class="card p-8 text-center">
            <div class="text-4xl mb-3">👤</div>
            <p class="text-white/60">Nog geen deelnemers.</p>
        </div>
    @else
        <div class="card overflow-hidden">
            @foreach($participants as $i => $p)
                <a href="/deelnemers/{{ $p['id'] }}" class="row {{ $p['id'] === auth()->id() ? 'row-me' : '' }}">
                    <span class="rank {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : '')) }}">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-white/90 text-sm truncate block">
                            {{ $p['name'] }}
                            @if($p['id'] === auth()->id())
                                <span class="text-volt-400 text-xs ml-1">(jij)</span>
                            @endif
                        </span>
                        <span class="text-xs text-white/40">
                            {{ $p['predictionsCount'] }} voorspelling{{ $p['predictionsCount'] !== 1 ? 'en' : '' }}
                        </span>
                    </div>
                    <span class="scoreline text-volt-400 flex-shrink-0">{{ $p['totalPoints'] }}pt</span>
                    <span class="text-white/20 flex-shrink-0">›</span>
                </a>
            @endforeach
        </div>
    @endif

</div>
@endsection
