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
        <form method="POST" action="/admin/sync">
            @csrf
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">
                🔄 Synchroniseer wedstrijden
            </button>
        </form>
    </section>

    {{-- Toernooi resultaat --}}
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">🥇 Toernooi resultaat</h2>
        <form method="POST" action="/admin/tournament">
            @csrf
            <div class="flex gap-3">
                <input type="text" name="top_scorer"
                    value="{{ old('top_scorer', $tournamentResult?->top_scorer) }}"
                    placeholder="Naam van de topscorer"
                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">
                    Opslaan
                </button>
            </div>
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
                        <div class="flex-1 flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-800">
                                {{ get_flag($fixture->home_team_code) }} {{ $fixture->home_team_code }}
                            </span>
                            @if($fixture->isFinished())
                                <span class="bg-gray-800 text-white text-xs font-bold px-2 py-0.5 rounded">
                                    {{ $fixture->home_score }}-{{ $fixture->away_score }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">vs</span>
                            @endif
                            <span class="text-sm font-semibold text-gray-800">
                                {{ $fixture->away_team_code }} {{ get_flag($fixture->away_team_code) }}
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
                                    <label class="block text-xs text-gray-500 mb-1">{{ $fixture->home_team_code }} score</label>
                                    <input type="number" name="home_score" min="0"
                                        value="{{ $fixture->home_score }}"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500"
                                        placeholder="0">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">{{ $fixture->away_team_code }} score</label>
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
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">1e gele kaart (min.)</label>
                                    <input type="number" name="first_yellow_card_minute" min="1" max="120"
                                        value="{{ $fixture->first_yellow_card_minute }}"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500"
                                        placeholder="bv. 15">
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
