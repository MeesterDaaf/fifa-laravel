@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold text-gray-800 mb-2">⚙️ Admin Panel</h1>
    <p class="text-gray-500 text-sm mb-6">{{ $totalUsers }} deelnemers geregistreerd</p>

    {{-- Vrienden uitnodigen --}}
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">👥 Vrienden uitnodigen</h2>

        @php $inviteLink = $baseUrl . '/register?code=' . $inviteCode; @endphp

        <div class="bg-gray-50 rounded-xl p-3 mb-3 flex items-center gap-2">
            <input type="text" id="inviteLink" value="{{ $inviteLink }}" readonly
                class="flex-1 bg-transparent text-sm text-gray-700 focus:outline-none font-mono truncate">
            <button onclick="copyInviteLink()" type="button"
                class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition-colors flex-shrink-0">
                Kopieer
            </button>
        </div>
        <p id="copiedMsg" class="text-xs text-green-600 hidden mb-3">✅ Link gekopieerd!</p>

        <form method="POST" action="/admin/invite/regenerate">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-red-500 transition-colors">
                🔄 Nieuwe code genereren
            </button>
        </form>
    </section>

    {{-- Wedstrijden synchroniseren --}}
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">🔄 Wedstrijden synchroniseren</h2>
        <p class="text-gray-500 text-sm mb-4">
            Haal de laatste wedstrijden op van football-data.org. Vereist een geldige API key in .env.
        </p>
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="/admin/sync">
                @csrf
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">
                    🔄 Synchroniseer wedstrijden
                </button>
            </form>
            <form method="POST" action="/admin/sync-squads">
                @csrf
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">
                    👥 Synchroniseer teams &amp; spelers
                </button>
            </form>
        </div>
    </section>

    {{-- Deelnemers beheren --}}
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">👤 Deelnemers beheren ({{ $users->count() }})</h2>
        @php $adminCount = $users->where('is_admin', true)->count(); @endphp
        <div class="divide-y divide-gray-50">
            @foreach($users as $u)
                @php $isLastAdmin = $u->is_admin && $adminCount <= 1; @endphp
                <div class="flex items-center gap-3 py-2.5">
                    <div class="flex-1 min-w-0">
                        <span class="text-sm font-medium text-gray-800 truncate block">
                            {{ $u->name }}
                            @if($u->is_admin)
                                <span class="text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded-full ml-1">admin</span>
                            @endif
                            @if($u->id === auth()->id())
                                <span class="text-green-600 text-xs ml-1">(jij)</span>
                            @endif
                        </span>
                        <span class="text-xs text-gray-400">{{ $u->email }}</span>
                    </div>
                    @if($u->id === auth()->id())
                        <span class="text-xs text-gray-300 shrink-0">jij</span>
                    @elseif($isLastAdmin)
                        <span class="text-xs text-gray-400 shrink-0" title="De laatste beheerder kan niet gewijzigd worden">🔒 laatste admin</span>
                    @else
                        <div class="flex items-center gap-2 shrink-0">
                            {{-- Promoten / intrekken --}}
                            <form method="POST" action="/admin/users/{{ $u->id }}/toggle-admin">
                                @csrf
                                @if($u->is_admin)
                                    <button type="submit"
                                        class="text-xs text-amber-600 hover:text-amber-800 border border-amber-200 hover:border-amber-400 rounded-lg px-3 py-1.5 transition-colors whitespace-nowrap">
                                        Admin intrekken
                                    </button>
                                @else
                                    <button type="submit"
                                        class="text-xs text-green-600 hover:text-green-800 border border-green-200 hover:border-green-400 rounded-lg px-3 py-1.5 transition-colors whitespace-nowrap">
                                        Maak admin
                                    </button>
                                @endif
                            </form>
                            {{-- Verwijderen --}}
                            <form method="POST" action="/admin/users/{{ $u->id }}"
                                onsubmit="return confirm('Deelnemer {{ $u->name }} en al hun voorspellingen verwijderen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-xs text-red-500 hover:text-red-700 border border-red-200 hover:border-red-400 rounded-lg px-3 py-1.5 transition-colors">
                                    Verwijderen
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- Toernooi resultaat --}}
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-1">🏆 Toernooi resultaat</h2>
        <p class="text-gray-500 text-sm mb-4">Vul de officiële uitslagen in. Bij opslaan worden alle toernooipunten herberekend.</p>
        <form method="POST" action="/admin/tournament" class="space-y-3">
            @csrf

            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">🏆 Toernooiwinnaar</label>
                    <select name="champion"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white">
                        <option value="">— Nog niet bekend —</option>
                        @foreach($teams as $team)
                            @php $teamName = country_name($team->tla, $team->name); @endphp
                            <option value="{{ $teamName }}" {{ $tournamentResult?->champion === $teamName ? 'selected' : '' }}>
                                {{ get_flag($team->tla) }} {{ $teamName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">🥇 Topscorer</label>
                    <input type="text" name="top_scorer"
                        value="{{ old('top_scorer', $tournamentResult?->top_scorer) }}"
                        placeholder="Naam van de topscorer"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">🟨 Totaal gele kaarten</label>
                    <input type="number" name="total_yellow_cards" min="0" max="2000"
                        value="{{ old('total_yellow_cards', $tournamentResult?->total_yellow_cards) }}"
                        placeholder="bv. 220"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">🟥 Totaal rode kaarten</label>
                    <input type="number" name="total_red_cards" min="0" max="500"
                        value="{{ old('total_red_cards', $tournamentResult?->total_red_cards) }}"
                        placeholder="bv. 12"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            </div>

            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">
                💾 Opslaan &amp; punten herberekenen
            </button>
        </form>
    </section>

    {{-- Wedstrijden beheren --}}
    <section>
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            ⚽ Wedstrijden ({{ $fixtures->count() }})
        </h2>

        @if($fixtures->isEmpty())
            <p class="text-gray-500 text-sm">Nog geen wedstrijden. Synchroniseer eerst.</p>
        @endif

        <div class="space-y-3">
            @foreach($fixtures as $fixture)
                <details class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <summary class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 rounded-xl select-none">
                        <div class="flex-1 flex items-center gap-2 min-w-0">
                            <span class="flex-1 text-sm font-semibold text-gray-800 truncate">
                                {{ get_flag($fixture->home_team_code) }} {{ country_name($fixture->home_team_code, $fixture->home_team) }}
                            </span>
                            @if($fixture->isFinished())
                                <span class="shrink-0 bg-gray-800 text-white text-xs font-bold px-2 py-0.5 rounded">
                                    {{ $fixture->home_score }}-{{ $fixture->away_score }}
                                </span>
                            @else
                                <span class="shrink-0 text-gray-400 text-xs">vs</span>
                            @endif
                            <span class="flex-1 text-sm font-semibold text-gray-800 truncate text-right">
                                {{ country_name($fixture->away_team_code, $fixture->away_team) }} {{ get_flag($fixture->away_team_code) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-xs text-gray-500">{{ format_date_short($fixture->scheduled_at) }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $fixture->status === 'FINISHED' ? 'bg-gray-100 text-gray-600' : ($fixture->status === 'IN_PLAY' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ $fixture->status === 'FINISHED' ? 'Afgelopen' : ($fixture->status === 'IN_PLAY' ? 'Live' : 'Gepland') }}
                            </span>
                            <span class="text-xs text-gray-400">👥 {{ $fixture->predictions_count }}</span>
                        </div>
                    </summary>

                    <div class="px-4 pb-4 pt-2 border-t border-gray-100">
                        <form method="POST" action="/admin/match/{{ $fixture->id }}">
                            @csrf
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500">
                                        <option value="SCHEDULED" {{ $fixture->status === 'SCHEDULED' ? 'selected' : '' }}>Gepland</option>
                                        <option value="IN_PLAY" {{ $fixture->status === 'IN_PLAY' ? 'selected' : '' }}>Live</option>
                                        <option value="FINISHED" {{ $fixture->status === 'FINISHED' ? 'selected' : '' }}>Afgelopen</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ country_name($fixture->home_team_code, $fixture->home_team) }} score</label>
                                    <input type="number" name="home_score" min="0"
                                        value="{{ $fixture->home_score }}"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500"
                                        placeholder="0">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ country_name($fixture->away_team_code, $fixture->away_team) }} score</label>
                                    <input type="number" name="away_score" min="0"
                                        value="{{ $fixture->away_score }}"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500"
                                        placeholder="0">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">1e doelpunt (min.)</label>
                                    <input type="number" name="first_goal_minute" min="1" max="120"
                                        value="{{ $fixture->first_goal_minute }}"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500"
                                        placeholder="bv. 23">
                                </div>
                            </div>
                            <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                                ✅ Opslaan & punten berekenen
                            </button>
                        </form>
                    </div>
                </details>
            @endforeach
        </div>
    </section>

</div>

<script>
function copyInviteLink() {
    const input = document.getElementById('inviteLink');
    navigator.clipboard.writeText(input.value).then(() => {
        document.getElementById('copiedMsg').classList.remove('hidden');
        setTimeout(() => document.getElementById('copiedMsg').classList.add('hidden'), 2000);
    });
}
</script>
@endsection
