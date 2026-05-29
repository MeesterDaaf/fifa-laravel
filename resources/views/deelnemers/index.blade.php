@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold text-gray-800 mb-2">👥 Deelnemers</h1>
    <p class="text-gray-500 text-sm mb-6">
        Bekijk de voorspellingen van alle deelnemers. Klik op een naam voor het volledige overzicht.
    </p>

    @if(empty($participants))
        <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
            <div class="text-4xl mb-3">👤</div>
            <p class="text-gray-500">Nog geen deelnemers.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @foreach($participants as $i => $p)
                <a href="/deelnemers/{{ $p['id'] }}"
                    class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0 transition-colors
                        {{ $p['id'] === auth()->id() ? 'bg-green-50' : 'hover:bg-gray-50' }}">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0
                        {{ $i === 0 ? 'bg-yellow-400 text-yellow-900' :
                           ($i === 1 ? 'bg-gray-300 text-gray-700' :
                           ($i === 2 ? 'bg-orange-300 text-orange-900' : 'bg-gray-100 text-gray-600')) }}">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-gray-800 text-sm truncate block">
                            {{ $p['name'] }}
                            @if($p['id'] === auth()->id())
                                <span class="text-green-600 text-xs ml-1">(jij)</span>
                            @endif
                        </span>
                        <span class="text-xs text-gray-400">
                            {{ $p['predictionsCount'] }} voorspelling{{ $p['predictionsCount'] !== 1 ? 'en' : '' }}
                        </span>
                    </div>
                    <span class="font-bold text-green-700 text-sm flex-shrink-0">{{ $p['totalPoints'] }}pt</span>
                    <span class="text-gray-300 flex-shrink-0">›</span>
                </a>
            @endforeach
        </div>
    @endif

</div>
@endsection
