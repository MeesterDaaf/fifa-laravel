<details class="relative shrink-0 group">
    <summary class="list-none cursor-pointer px-2 py-1 select-none [&::-webkit-details-marker]:hidden">
        <span class="relative inline-flex items-center">
            <span class="text-lg">🔔</span>
            @if($todoCount > 0)
                <span class="absolute -top-1.5 -right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                    {{ $todoCount }}
                </span>
            @endif
        </span>
    </summary>

    <div class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
        <div class="px-4 py-2.5 border-b border-gray-100 bg-gray-50">
            <p class="text-sm font-semibold text-gray-700">📅 Eerstvolgende wedstrijden</p>
            @if($todoCount > 0)
                <p class="text-xs text-amber-600 mt-0.5">Je moet er nog {{ $todoCount }} voorspellen</p>
            @elseif($next->isNotEmpty())
                <p class="text-xs text-green-600 mt-0.5">Allemaal voorspeld ✅</p>
            @endif
        </div>

        @forelse($next as $match)
            @php $predicted = isset($predictedIds[$match->id]); @endphp
            <a href="/voorspellingen/{{ $match->id }}"
                class="flex items-center gap-2 px-4 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-800 truncate">
                        {{ get_flag($match->home_team_code) }} {{ country_name($match->home_team_code, $match->home_team) }}
                        <span class="text-gray-400 text-xs">vs</span>
                        {{ country_name($match->away_team_code, $match->away_team) }} {{ get_flag($match->away_team_code) }}
                    </div>
                    <div class="text-xs text-gray-400">{{ format_date_short($match->scheduled_at) }}</div>
                </div>
                @if($predicted)
                    <span class="text-xs text-green-600 font-medium shrink-0">✅</span>
                @else
                    <span class="text-xs text-orange-500 font-medium shrink-0">⏳</span>
                @endif
            </a>
        @empty
            <p class="px-4 py-4 text-sm text-gray-400 text-center">Geen aankomende wedstrijden</p>
        @endforelse

        <a href="/voorspellingen" class="block text-center text-sm text-green-600 hover:bg-green-50 py-2.5 font-medium">
            Alle wedstrijden →
        </a>
    </div>
</details>
