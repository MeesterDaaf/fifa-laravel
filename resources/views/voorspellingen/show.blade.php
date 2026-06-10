@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 stagger">

    {{-- Wedstrijd header --}}
    <div class="card-volt p-4 sm:p-6 mb-4 relative overflow-hidden">
        <div class="text-xs sm:text-sm text-white/50 mb-3 text-center font-display uppercase tracking-[0.2em]">{{ format_date($fixture->scheduled_at) }}</div>
        <div class="flex items-center justify-between gap-4">

            <div class="flex-1 text-center">
                <div class="text-4xl sm:text-5xl mb-2 drop-shadow-[0_0_12px_rgba(189,240,59,0.25)]">{{ get_flag($fixture->home_team_code) }}</div>
                <div class="font-display font-bold uppercase text-base sm:text-lg leading-tight text-white">{{ country_name($fixture->home_team_code, $fixture->home_team) }}</div>
            </div>

            @if($fixture->isFinished())
                <div class="text-center">
                    <div class="scoreline text-4xl sm:text-5xl text-volt-400">{{ $fixture->home_score }}-{{ $fixture->away_score }}</div>
                    <div class="text-white/45 text-xs mt-1 uppercase tracking-widest font-display">Eindstand</div>
                </div>
            @else
                <div class="scoreline text-2xl sm:text-3xl text-white/25 italic">VS</div>
            @endif

            <div class="flex-1 text-center">
                <div class="text-4xl sm:text-5xl mb-2 drop-shadow-[0_0_12px_rgba(189,240,59,0.25)]">{{ get_flag($fixture->away_team_code) }}</div>
                <div class="font-display font-bold uppercase text-base sm:text-lg leading-tight text-white">{{ country_name($fixture->away_team_code, $fixture->away_team) }}</div>
            </div>
        </div>

        @if($fixture->match_group)
            <div class="text-center text-volt-500/80 font-display uppercase tracking-[0.2em] text-xs mt-4">{{ group_label($fixture->match_group) }}</div>
        @endif

        @if($fixture->isFinished() && $fixture->first_goal_minute !== null)
            <div class="mt-4 flex justify-center text-center text-sm">
                <div class="bg-white/5 border border-white/10 rounded-lg py-2 px-6">
                    <div class="scoreline text-white text-lg">{{ $fixture->first_goal_minute }}'</div>
                    <div class="text-white/45 text-xs">1e doelpunt</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Voorspelformulier --}}
    @if($fixture->isOpen())
        <div class="card p-6 mb-6">
            <h3 class="font-display font-bold uppercase tracking-wide text-white mb-1">
                {{ $myPrediction ? 'Voorspelling aanpassen' : 'Jouw voorspelling' }}
            </h3>
            <p class="text-xs text-white/40 mb-4">⏳ Sluit {{ format_date($fixture->locksAt()) }}</p>

            <form method="POST" action="/voorspellingen/{{ $fixture->id }}" id="predictionForm">
                @csrf

                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-1 text-center">
                        <label class="label text-center">{{ country_name($fixture->home_team_code, $fixture->home_team) }}</label>
                        <input type="number" name="home_score" min="0" max="30" inputmode="numeric"
                            value="{{ old('home_score', $myPrediction?->home_score ?? '') }}"
                            class="input input-score"
                            placeholder="0" required>
                    </div>
                    <div class="scoreline text-2xl text-white/30 mt-5">-</div>
                    <div class="flex-1 text-center">
                        <label class="label text-center">{{ country_name($fixture->away_team_code, $fixture->away_team) }}</label>
                        <input type="number" name="away_score" min="0" max="30" inputmode="numeric"
                            value="{{ old('away_score', $myPrediction?->away_score ?? '') }}"
                            class="input input-score"
                            placeholder="0" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="label">⚽ 1e doelpunt (minuut, optioneel)</label>
                    <input type="number" name="first_goal_minute" min="1" max="120" inputmode="numeric"
                        value="{{ old('first_goal_minute', $myPrediction?->first_goal_minute) }}"
                        class="input"
                        placeholder="bv. 23">
                </div>

                @if($errors->any())
                    <div class="alert alert-error mb-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <button type="submit" class="btn btn-volt w-full py-3">
                    {{ $myPrediction ? '✓ Voorspelling opslaan' : '⚽ Voorspelling opslaan' }}
                </button>

                <p id="autosaveStatus" class="text-center text-xs text-white/40 mt-2 h-4" aria-live="polite"></p>
            </form>

            @if($nextFixture)
                <a href="/voorspellingen/{{ $nextFixture->id }}" id="nextMatchLink"
                    class="btn btn-outline w-full mt-3 py-3 {{ $myPrediction ? '' : 'hidden' }}">
                    <span>Volgende wedstrijd</span>
                    <span class="font-bold normal-case">{{ get_flag($nextFixture->home_team_code) }} {{ $nextFixture->home_team_code }}–{{ $nextFixture->away_team_code }} {{ get_flag($nextFixture->away_team_code) }}</span>
                    <span aria-hidden="true">→</span>
                </a>
            @else
                <a href="/voorspellingen" id="nextMatchLink"
                    class="btn btn-outline w-full mt-3 py-3 {{ $myPrediction ? '' : 'hidden' }}">
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
                    status.className = 'text-center text-xs mt-2 h-4 ' + (color || 'text-white/40');
                }

                async function save(silent) {
                    const home = form.querySelector('input[name="home_score"]').value;
                    const away = form.querySelector('input[name="away_score"]').value;
                    // Beide scores nodig; bij autosave wachten we tot ze ingevuld zijn.
                    if (home === '' || away === '') {
                        if (!silent) setStatus('Vul beide scores in', 'text-signal-amber');
                        return;
                    }
                    setStatus('Opslaan…', 'text-white/40');
                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            body: new FormData(form),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok) {
                            const t = new Date().toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' });
                            setStatus('✓ Automatisch opgeslagen om ' + t, 'text-volt-400');
                            const next = document.getElementById('nextMatchLink');
                            next?.classList.remove('hidden');
                            // Alleen na een expliciete opslag (knop) de focus verplaatsen,
                            // niet tijdens het typen — anders springt de focus uit het veld.
                            if (!silent && next) {
                                next.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                next.focus();
                            }
                        } else {
                            setStatus('⚠️ ' + (data.message || 'Opslaan mislukt'), 'text-signal-red');
                        }
                    } catch (e) {
                        setStatus('⚠️ Geen verbinding — niet opgeslagen', 'text-signal-red');
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
        <div class="card p-4 text-center text-white/50 text-sm mb-6">
            🔒 Voorspellen gesloten — sluit {{ \App\Models\Fixture::LOCK_MINUTES }} minuten vóór aanvang
        </div>
    @endif

    {{-- Winkans (Elo-model) --}}
    @if($probability['known'])
        <div class="card p-4 mb-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-display font-bold uppercase tracking-wide text-white text-sm">📊 Winkans</h3>
                <span class="text-xs text-white/40">o.b.v. Elo-rating</span>
            </div>
            <div class="flex items-center justify-between text-sm font-semibold mb-2 scoreline">
                <span class="text-volt-400">{{ get_flag($fixture->home_team_code) }} {{ $probability['home'] }}%</span>
                <span class="text-white/45">Gelijk {{ $probability['draw'] }}%</span>
                <span class="text-signal-blue">{{ $probability['away'] }}% {{ get_flag($fixture->away_team_code) }}</span>
            </div>
            <div class="flex h-3 rounded-full overflow-hidden bg-white/5">
                <div class="bg-volt-500" style="width: {{ $probability['home'] }}%"></div>
                <div class="bg-white/20" style="width: {{ $probability['draw'] }}%"></div>
                <div class="bg-signal-blue" style="width: {{ $probability['away'] }}%"></div>
            </div>
            <p class="text-xs text-white/35 mt-2">Statistische schatting — geen garantie.</p>
        </div>
    @endif

    {{-- Puntensysteem --}}
    <div class="alert alert-info mb-6">
        <p class="font-display font-bold uppercase tracking-wide mb-1">🎯 Puntensysteem</p>
        <ul class="space-y-0.5 text-xs text-white/60">
            <li>⚽ Exacte uitslag: <strong class="text-white/85">{{ config('scoring.match.exact') }} punten</strong></li>
            <li>✓ Juiste winnaar/gelijkspel: <strong class="text-white/85">{{ config('scoring.match.outcome') }} punten</strong></li>
            <li>🕐 Dichtstbijzijnde 1e doelpuntminuut: <strong class="text-white/85">+{{ config('scoring.match.goal_minute_bonus') }} bonuspunten</strong></li>
        </ul>
    </div>

    {{-- Jouw voorspelling (na sluiting) --}}
    @if($myPrediction && !$fixture->isOpen())
        <div class="card p-4 mb-4">
            <h3 class="font-display font-bold uppercase tracking-wide text-white mb-3">Jouw voorspelling</h3>
            <div class="flex items-center justify-between">
                <div class="text-sm">
                    <span class="font-bold text-white/85">{{ country_name($fixture->home_team_code, $fixture->home_team) }}</span>
                    <span class="mx-2 scoreline text-xl text-volt-400">
                        {{ $myPrediction->home_score }} - {{ $myPrediction->away_score }}
                    </span>
                    <span class="font-bold text-white/85">{{ country_name($fixture->away_team_code, $fixture->away_team) }}</span>
                </div>
                @if($fixture->isFinished())
                    <span class="scoreline text-volt-400 text-lg">{{ $myPrediction->total_points }} pt</span>
                @endif
            </div>
            @if($myPrediction->first_goal_minute !== null)
                <p class="text-xs text-white/45 mt-1">1e doelpunt: minuut {{ $myPrediction->first_goal_minute }}</p>
            @endif
            @if($fixture->isFinished() && $myPrediction->total_points > 0)
                <div class="mt-2 text-xs text-white/45 space-y-0.5">
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
        <div class="card overflow-hidden">
            <div class="px-4 py-3 border-b border-white/8 font-display font-bold uppercase tracking-wide text-white">
                📋 Alle voorspellingen
            </div>
            @foreach($allPredictions as $i => $pred)
                <div class="row {{ $pred->user_id === auth()->id() ? 'row-me' : '' }}">
                    <span class="text-sm text-white/35 w-5 scoreline">{{ $i + 1 }}</span>
                    <span class="flex-1 text-sm font-medium text-white/90">
                        {{ $pred->user->name }}
                        @if($pred->user_id === auth()->id())
                            <span class="text-volt-400 text-xs ml-1">(jij)</span>
                        @endif
                    </span>
                    <span class="text-sm scoreline text-white/75">{{ $pred->home_score }}-{{ $pred->away_score }}</span>
                    @if($pred->first_goal_minute !== null)
                        <span class="text-xs text-white/45">⚽{{ $pred->first_goal_minute }}'</span>
                    @endif
                    @if($pred->points_goal_minute > 0)
                        <span class="text-xs font-semibold text-volt-400" title="Dichtst bij de 1e doelpuntminuut">🎯+{{ $pred->points_goal_minute }}</span>
                    @endif
                    <span class="scoreline text-volt-400 w-12 text-right">{{ $pred->total_points }}pt</span>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
