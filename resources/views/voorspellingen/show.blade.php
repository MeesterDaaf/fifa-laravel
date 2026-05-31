@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">

    {{-- Wedstrijd header --}}
    <div class="bg-gradient-to-r from-green-700 to-green-600 rounded-2xl p-4 sm:p-6 text-white mb-4">
        <div class="text-xs sm:text-sm text-green-200 mb-2">{{ format_date($fixture->scheduled_at) }}</div>
        <div class="flex items-center justify-between gap-4">

            <div class="flex-1 text-center">
                <div class="text-3xl sm:text-4xl mb-1">{{ get_flag($fixture->home_team_code) }}</div>
                <div class="font-bold text-base sm:text-lg leading-tight">{{ country_name($fixture->home_team_code, $fixture->home_team) }}</div>
            </div>

            @if($fixture->isFinished())
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl font-black">{{ $fixture->home_score }} - {{ $fixture->away_score }}</div>
                    <div class="text-green-300 text-xs mt-1">Eindstand</div>
                </div>
            @else
                <div class="text-xl sm:text-2xl font-bold text-green-300">VS</div>
            @endif

            <div class="flex-1 text-center">
                <div class="text-3xl sm:text-4xl mb-1">{{ get_flag($fixture->away_team_code) }}</div>
                <div class="font-bold text-base sm:text-lg leading-tight">{{ country_name($fixture->away_team_code, $fixture->away_team) }}</div>
            </div>
        </div>

        @if($fixture->match_group)
            <div class="text-center text-green-300 text-xs mt-3">{{ group_label($fixture->match_group) }}</div>
        @endif

        @if($fixture->isFinished() && $fixture->first_goal_minute !== null)
            <div class="mt-4 flex justify-center text-center text-sm">
                <div class="bg-white/10 rounded-lg py-2 px-6">
                    <div class="text-white font-bold">{{ $fixture->first_goal_minute }}'</div>
                    <div class="text-green-300 text-xs">1e doelpunt</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Voorspelformulier --}}
    @if($fixture->isOpen())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="font-semibold text-gray-700 mb-1">
                {{ $myPrediction ? 'Voorspelling aanpassen' : 'Jouw voorspelling' }}
            </h3>
            <p class="text-xs text-gray-400 mb-4">⏳ Sluit {{ format_date($fixture->locksAt()) }}</p>

            <form method="POST" action="/voorspellingen/{{ $fixture->id }}" id="predictionForm">
                @csrf

                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-1 text-center">
                        <label class="block text-xs text-gray-500 mb-1">{{ country_name($fixture->home_team_code, $fixture->home_team) }}</label>
                        <input type="number" name="home_score" min="0" max="30" inputmode="numeric"
                            value="{{ old('home_score', $myPrediction?->home_score ?? '') }}"
                            class="w-full text-center text-2xl font-bold border-2 border-gray-200 rounded-xl py-3 focus:outline-none focus:border-green-500"
                            placeholder="0" required>
                    </div>
                    <div class="text-xl font-bold text-gray-400">-</div>
                    <div class="flex-1 text-center">
                        <label class="block text-xs text-gray-500 mb-1">{{ country_name($fixture->away_team_code, $fixture->away_team) }}</label>
                        <input type="number" name="away_score" min="0" max="30" inputmode="numeric"
                            value="{{ old('away_score', $myPrediction?->away_score ?? '') }}"
                            class="w-full text-center text-2xl font-bold border-2 border-gray-200 rounded-xl py-3 focus:outline-none focus:border-green-500"
                            placeholder="0" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-1">⚽ 1e doelpunt (minuut, optioneel)</label>
                    <input type="number" name="first_goal_minute" min="1" max="120" inputmode="numeric"
                        value="{{ old('first_goal_minute', $myPrediction?->first_goal_minute) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500"
                        placeholder="bv. 23">
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition-colors">
                    {{ $myPrediction ? '✅ Voorspelling opslaan' : '⚽ Voorspelling opslaan' }}
                </button>

                <p id="autosaveStatus" class="text-center text-xs text-gray-400 mt-2 h-4" aria-live="polite"></p>
            </form>

            @if($nextFixture)
                <a href="/voorspellingen/{{ $nextFixture->id }}" id="nextMatchLink"
                    class="flex items-center justify-center gap-2 w-full mt-3 bg-white border-2 border-green-600 text-green-700 font-semibold py-3 rounded-xl hover:bg-green-50 transition-colors focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-1 {{ $myPrediction ? '' : 'hidden' }}">
                    <span>Volgende wedstrijd</span>
                    <span class="font-bold">{{ get_flag($nextFixture->home_team_code) }} {{ $nextFixture->home_team_code }}–{{ $nextFixture->away_team_code }} {{ get_flag($nextFixture->away_team_code) }}</span>
                    <span aria-hidden="true">→</span>
                </a>
            @else
                <a href="/voorspellingen" id="nextMatchLink"
                    class="flex items-center justify-center gap-2 w-full mt-3 bg-white border-2 border-green-600 text-green-700 font-semibold py-3 rounded-xl hover:bg-green-50 transition-colors focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-1 {{ $myPrediction ? '' : 'hidden' }}">
                    ✓ Alles voorspeld — naar overzicht
                </a>
            @endif

            <script>
            (function () {
                const form = document.getElementById('predictionForm');
                if (!form) return;
                const status = document.getElementById('autosaveStatus');
                const fields = form.querySelectorAll('input[name="home_score"], input[name="away_score"], input[name="first_goal_minute"]');
                let timer = null;

                function setStatus(text, color) {
                    status.textContent = text;
                    status.className = 'text-center text-xs mt-2 h-4 ' + (color || 'text-gray-400');
                }

                async function save(silent) {
                    const home = form.querySelector('input[name="home_score"]').value;
                    const away = form.querySelector('input[name="away_score"]').value;
                    // Beide scores nodig; bij autosave wachten we tot ze ingevuld zijn.
                    if (home === '' || away === '') {
                        if (!silent) setStatus('Vul beide scores in', 'text-orange-500');
                        return;
                    }
                    setStatus('Opslaan…', 'text-gray-400');
                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            body: new FormData(form),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok) {
                            const t = new Date().toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' });
                            setStatus('✓ Automatisch opgeslagen om ' + t, 'text-green-600');
                            const next = document.getElementById('nextMatchLink');
                            next?.classList.remove('hidden');
                            // Alleen na een expliciete opslag (knop) de focus verplaatsen,
                            // niet tijdens het typen — anders springt de focus uit het veld.
                            if (!silent && next) {
                                next.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                next.focus();
                            }
                        } else {
                            setStatus('⚠️ ' + (data.message || 'Opslaan mislukt'), 'text-red-500');
                        }
                    } catch (e) {
                        setStatus('⚠️ Geen verbinding — niet opgeslagen', 'text-red-500');
                    }
                }

                // Auto-opslaan tijdens typen (met kleine vertraging).
                fields.forEach(function (el) {
                    el.addEventListener('input', function () {
                        clearTimeout(timer);
                        timer = setTimeout(() => save(true), 700);
                    });
                    el.addEventListener('change', () => { clearTimeout(timer); save(true); });
                });

                // De knop blijft werken: met JS slaat 'ie via AJAX op (geen herlaad).
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    clearTimeout(timer);
                    save(false);
                });
            })();
            </script>
        </div>
    @endif

    @if(!$fixture->isOpen() && !$fixture->isFinished())
        <div class="bg-gray-100 rounded-xl p-4 text-center text-gray-500 text-sm mb-6">
            🔒 Voorspellen gesloten — sluit {{ \App\Models\Fixture::LOCK_MINUTES }} minuten vóór aanvang
        </div>
    @endif

    {{-- Winkans (Elo-model) --}}
    @if($probability['known'])
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold text-gray-700 text-sm">📊 Winkans</h3>
                <span class="text-xs text-gray-400">o.b.v. Elo-rating</span>
            </div>
            <div class="flex items-center justify-between text-sm font-semibold mb-2">
                <span class="text-green-700">{{ get_flag($fixture->home_team_code) }} {{ $probability['home'] }}%</span>
                <span class="text-gray-500">Gelijk {{ $probability['draw'] }}%</span>
                <span class="text-blue-700">{{ $probability['away'] }}% {{ get_flag($fixture->away_team_code) }}</span>
            </div>
            <div class="flex h-3 rounded-full overflow-hidden bg-gray-100">
                <div class="bg-green-500" style="width: {{ $probability['home'] }}%"></div>
                <div class="bg-gray-300" style="width: {{ $probability['draw'] }}%"></div>
                <div class="bg-blue-500" style="width: {{ $probability['away'] }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Statistische schatting — geen garantie.</p>
        </div>
    @endif

    {{-- Puntensysteem --}}
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 text-sm text-blue-800">
        <p class="font-semibold mb-1">🎯 Puntensysteem</p>
        <ul class="space-y-0.5 text-xs text-blue-700">
            <li>⚽ Exacte uitslag: <strong>{{ config('scoring.match.exact') }} punten</strong></li>
            <li>✅ Juiste winnaar/gelijkspel: <strong>{{ config('scoring.match.outcome') }} punten</strong></li>
            <li>🕐 Dichtstbijzijnde 1e doelpuntminuut: <strong>+{{ config('scoring.match.goal_minute_bonus') }} bonuspunten</strong></li>
        </ul>
    </div>

    {{-- Jouw voorspelling (na sluiting) --}}
    @if($myPrediction && !$fixture->isOpen())
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-4">
            <h3 class="font-semibold text-gray-700 mb-3">Jouw voorspelling</h3>
            <div class="flex items-center justify-between">
                <div class="text-sm">
                    <span class="font-bold text-gray-800">{{ country_name($fixture->home_team_code, $fixture->home_team) }}</span>
                    <span class="mx-2 text-xl font-black text-green-700">
                        {{ $myPrediction->home_score }} - {{ $myPrediction->away_score }}
                    </span>
                    <span class="font-bold text-gray-800">{{ country_name($fixture->away_team_code, $fixture->away_team) }}</span>
                </div>
                @if($fixture->isFinished())
                    <span class="text-green-700 font-bold text-lg">{{ $myPrediction->total_points }} pt</span>
                @endif
            </div>
            @if($myPrediction->first_goal_minute !== null)
                <p class="text-xs text-gray-500 mt-1">1e doelpunt: minuut {{ $myPrediction->first_goal_minute }}</p>
            @endif
            @if($fixture->isFinished() && $myPrediction->total_points > 0)
                <div class="mt-2 text-xs text-gray-500 space-y-0.5">
                    <p>Score punten: {{ $myPrediction->points_score }}pt</p>
                    @if($myPrediction->points_goal_minute > 0)
                        <p>⚽ Bonus doelpuntminuut: +{{ $myPrediction->points_goal_minute }}pt</p>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Alle voorspellingen na afloop --}}
    @if($fixture->isFinished() && $allPredictions->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 font-semibold text-gray-700">
                📋 Alle voorspellingen
            </div>
            @foreach($allPredictions as $i => $pred)
                <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 last:border-0
                    {{ $pred->user_id === auth()->id() ? 'bg-green-50' : '' }}">
                    <span class="text-sm text-gray-400 w-5">{{ $i + 1 }}</span>
                    <span class="flex-1 text-sm font-medium text-gray-800">
                        {{ $pred->user->name }}
                        @if($pred->user_id === auth()->id())
                            <span class="text-green-600 text-xs ml-1">(jij)</span>
                        @endif
                    </span>
                    <span class="text-sm font-mono text-gray-700">{{ $pred->home_score }}-{{ $pred->away_score }}</span>
                    @if($pred->first_goal_minute !== null)
                        <span class="text-xs text-gray-500">⚽{{ $pred->first_goal_minute }}'</span>
                    @endif
                    <span class="font-bold text-green-700 w-12 text-right">{{ $pred->total_points }}pt</span>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
