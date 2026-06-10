@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 stagger">

    <p class="kicker mb-1">Beheer</p>
    <h1 class="h-display text-4xl mb-2">Admin <span class="text-volt-400">Panel</span></h1>
    <p class="text-white/55 text-sm mb-6">{{ $totalUsers }} deelnemers geregistreerd</p>

    {{-- Waarschuwing: speelronde voorbij, uitslagen wachten op invoer --}}
    @if($awaitingResults->isNotEmpty())
        <div class="alert alert-warn p-5 mb-6">
            <h2 class="font-display font-bold uppercase tracking-wide flex items-center gap-2">
                ⚠️ {{ $awaitingResults->count() }} wedstrijd{{ $awaitingResults->count() !== 1 ? 'en' : '' }} wacht{{ $awaitingResults->count() === 1 ? '' : 'en' }} op invoer
            </h2>
            <p class="text-sm text-white/60 mt-1 mb-3">
                Deze zijn gespeeld maar nog niet ingevoerd. <strong class="text-white/85">Tip:</strong> leg eerst de ranglijst vast (knop hieronder), voer daarna de uitslagen in — dan kloppen de stijgers/dalers.
            </p>
            <ul class="text-sm text-white/75 space-y-1">
                @foreach($awaitingResults as $m)
                    <li>{{ get_flag($m->home_team_code) }} {{ country_name($m->home_team_code, $m->home_team) }} – {{ country_name($m->away_team_code, $m->away_team) }} {{ get_flag($m->away_team_code) }} <span class="text-white/40">({{ format_date($m->scheduled_at) }})</span></li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Ranglijst-ijkpunt --}}
    <section class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-2">📊 Ranglijst vastleggen</h2>
        <p class="text-white/55 text-sm mb-4">
            Leg de huidige stand vast als ijkpunt; daarna tonen de stijgers/dalers (▲/▼) op de ranglijst de beweging sinds dit punt.
            Doe dit aan het begin van een speelronde, vóór je de uitslagen invoert.
            @if($rankingCapturedAt)
                <br><span class="text-xs text-white/40">Laatst vastgelegd: {{ format_date($rankingCapturedAt) }}</span>
            @endif
        </p>
        <form method="POST" action="/admin/capture-ranking">
            @csrf
            <button type="submit" class="btn btn-outline">
                📸 Ranglijst vastleggen
            </button>
        </form>
    </section>

    {{-- Vrienden uitnodigen --}}
    <section class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-4">👥 Vrienden uitnodigen</h2>

        @php $inviteLink = $baseUrl . '/register?code=' . $inviteCode; @endphp

        <div class="bg-pitch-950/60 border border-white/10 rounded-xl p-3 mb-3 flex items-center gap-2">
            <input type="text" id="inviteLink" value="{{ $inviteLink }}" readonly
                class="flex-1 bg-transparent text-sm text-white/75 focus:outline-none font-mono truncate">
            <button onclick="copyInviteLink()" type="button"
                class="btn btn-volt text-xs px-3 py-1.5 flex-shrink-0">
                Kopieer
            </button>
        </div>
        <p id="copiedMsg" class="text-xs text-volt-400 hidden mb-3">✓ Link gekopieerd!</p>

        <form method="POST" action="/admin/invite/regenerate">
            @csrf
            <button type="submit" class="text-sm text-white/50 hover:text-signal-red transition-colors cursor-pointer">
                🔄 Nieuwe code genereren
            </button>
        </form>
    </section>

    {{-- WhatsApp-groep --}}
    <section class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-2">💬 WhatsApp-groep</h2>
        <p class="text-white/55 text-sm mb-4">
            Plak hier de uitnodigingslink van je WhatsApp-groep (WhatsApp → groep → "Uitnodigen via link").
            Deelnemers zien dan een knop op het dashboard om zelf de groep in te gaan.
        </p>

        <form method="POST" action="/admin/whatsapp" class="flex gap-3 mb-4">
            @csrf
            <input type="url" name="whatsapp_group_url"
                value="{{ old('whatsapp_group_url', $whatsappGroupUrl) }}"
                placeholder="https://chat.whatsapp.com/..."
                class="input flex-1">
            <button type="submit" class="btn btn-volt">
                Opslaan
            </button>
        </form>

        @if($whatsappReminderText)
            <p class="text-xs text-white/50 mb-2">Klaargezette reminder (jij kiest de groep en verstuurt):</p>
            <div class="bg-pitch-950/60 border border-white/10 rounded-xl p-3 text-sm text-white/65 mb-3">{{ $whatsappReminderText }}</div>
            <a href="https://wa.me/?text={{ rawurlencode($whatsappReminderText) }}" target="_blank" rel="noopener"
                class="btn btn-wa">
                📣 Stuur reminder via WhatsApp
            </a>
        @else
            <p class="text-sm text-white/40">Geen open wedstrijden om een reminder voor te maken.</p>
        @endif
    </section>

    {{-- Wedstrijden synchroniseren --}}
    <section class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-2">🔄 Wedstrijden synchroniseren</h2>
        <p class="text-white/55 text-sm mb-4">
            Haal de laatste wedstrijden op van football-data.org. Vereist een geldige API key in .env.
        </p>
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="/admin/sync">
                @csrf
                <button type="submit" class="btn btn-outline">
                    🔄 Synchroniseer wedstrijden
                </button>
            </form>
            <form method="POST" action="/admin/sync-squads">
                @csrf
                <button type="submit" class="btn btn-outline">
                    👥 Synchroniseer teams &amp; spelers
                </button>
            </form>
        </div>
    </section>

    {{-- Herinneringen --}}
    <section class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-2">✉️ Herinneringen</h2>
        <p class="text-white/55 text-sm mb-4">
            Stuur een e-mail naar iedereen die de wedstrijden van <strong class="text-white/85">morgen</strong> nog niet heeft voorspeld.
            Dit gebeurt ook automatisch elke dag om 18:00 (via cron).
        </p>
        <form method="POST" action="/admin/send-reminders"
            onsubmit="return confirm('Herinneringen versturen voor de wedstrijden van morgen?');">
            @csrf
            <button type="submit" class="btn btn-outline">
                ✉️ Stuur herinneringen voor morgen
            </button>
        </form>
    </section>

    {{-- AI-bot --}}
    <section class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-2">🤖 AI-speler</h2>
        <p class="text-white/55 text-sm mb-4">
            Laat de AI-bot voorspellingen doen voor alle open wedstrijden (en eenmalig het toernooi).
            Dit gebeurt ook automatisch elke dag om 12:00.
        </p>
        <form method="POST" action="/admin/ai-predict">
            @csrf
            <button type="submit" class="btn btn-outline">
                🤖 Laat de AI voorspellen
            </button>
        </form>
    </section>

    {{-- Deelnemers beheren --}}
    <section class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-4">👤 Deelnemers beheren ({{ $users->count() }})</h2>
        @php $adminCount = $users->where('is_admin', true)->count(); @endphp
        <div class="divide-y divide-white/5">
            @foreach($users as $u)
                @php $isLastAdmin = $u->is_admin && $adminCount <= 1; @endphp
                <div class="flex items-center gap-3 py-2.5">
                    <div class="flex-1 min-w-0">
                        <span class="text-sm font-medium text-white/90 truncate block">
                            {{ $u->name }}
                            @if($u->is_admin)
                                <span class="pill pill-ok ml-1">admin</span>
                            @endif
                            @if($u->id === auth()->id())
                                <span class="text-volt-400 text-xs ml-1">(jij)</span>
                            @endif
                        </span>
                        <span class="text-xs text-white/40">{{ $u->email }}</span>
                    </div>
                    @if($u->id === auth()->id())
                        <span class="text-xs text-white/25 shrink-0">jij</span>
                    @elseif($isLastAdmin)
                        <span class="text-xs text-white/40 shrink-0" title="De laatste beheerder kan niet gewijzigd worden">🔒 laatste admin</span>
                    @else
                        <div class="flex items-center gap-2 shrink-0">
                            {{-- Herinnering sturen --}}
                            <form method="POST" action="/admin/users/{{ $u->id }}/remind">
                                @csrf
                                <button type="submit" title="Stuur een herinnering voor morgen"
                                    class="text-xs text-signal-amber hover:text-white border border-signal-amber/30 hover:border-signal-amber/60 rounded-lg px-3 py-1.5 transition-colors whitespace-nowrap cursor-pointer">
                                    ✉️ Herinner
                                </button>
                            </form>
                            {{-- Wachtwoord-resetlink sturen --}}
                            <form method="POST" action="/admin/users/{{ $u->id }}/send-reset">
                                @csrf
                                <button type="submit" title="Stuur een resetlink naar {{ $u->email }}"
                                    class="text-xs text-signal-blue hover:text-white border border-signal-blue/30 hover:border-signal-blue/60 rounded-lg px-3 py-1.5 transition-colors whitespace-nowrap cursor-pointer">
                                    🔑 Reset-link
                                </button>
                            </form>
                            {{-- Promoten / intrekken --}}
                            <form method="POST" action="/admin/users/{{ $u->id }}/toggle-admin">
                                @csrf
                                @if($u->is_admin)
                                    <button type="submit"
                                        class="text-xs text-signal-amber hover:text-white border border-signal-amber/30 hover:border-signal-amber/60 rounded-lg px-3 py-1.5 transition-colors whitespace-nowrap cursor-pointer">
                                        Admin intrekken
                                    </button>
                                @else
                                    <button type="submit"
                                        class="text-xs text-volt-400 hover:text-white border border-volt-500/30 hover:border-volt-500/60 rounded-lg px-3 py-1.5 transition-colors whitespace-nowrap cursor-pointer">
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
                                    class="text-xs text-signal-red hover:text-white border border-signal-red/30 hover:border-signal-red/60 rounded-lg px-3 py-1.5 transition-colors cursor-pointer">
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
    <section class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-1">🏆 Toernooi resultaat</h2>
        <p class="text-white/55 text-sm mb-4">Vul de officiële uitslagen in. Bij opslaan worden alle toernooipunten herberekend.</p>
        <form method="POST" action="/admin/tournament" class="space-y-3">
            @csrf

            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="label">🏆 Toernooiwinnaar</label>
                    <select name="champion" class="input">
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
                    <label class="label">🥇 Topscorer</label>
                    <input type="text" name="top_scorer"
                        value="{{ old('top_scorer', $tournamentResult?->top_scorer) }}"
                        placeholder="Naam van de topscorer"
                        class="input">
                </div>
                <div>
                    <label class="label">🟨 Totaal gele kaarten</label>
                    <input type="number" inputmode="numeric" name="total_yellow_cards" min="0" max="2000"
                        value="{{ old('total_yellow_cards', $tournamentResult?->total_yellow_cards) }}"
                        placeholder="bv. 220"
                        class="input">
                </div>
                <div>
                    <label class="label">🟥 Totaal rode kaarten</label>
                    <input type="number" inputmode="numeric" name="total_red_cards" min="0" max="500"
                        value="{{ old('total_red_cards', $tournamentResult?->total_red_cards) }}"
                        placeholder="bv. 12"
                        class="input">
                </div>
            </div>

            <button type="submit" class="btn btn-volt">
                💾 Opslaan &amp; punten herberekenen
            </button>
        </form>
    </section>

    {{-- Wedstrijden beheren --}}
    <section>
        <h2 class="font-display font-bold uppercase tracking-wide text-lg text-white mb-4">
            ⚽ Wedstrijden ({{ $fixtures->count() }})
        </h2>

        @if($fixtures->isEmpty())
            <p class="text-white/50 text-sm">Nog geen wedstrijden. Synchroniseer eerst.</p>
        @endif

        <div class="space-y-3">
            @foreach($fixtures as $fixture)
                <details class="card">
                    <summary class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-white/4 rounded-2xl select-none">
                        <div class="flex-1 flex items-center gap-2 min-w-0">
                            <span class="flex-1 text-sm font-semibold text-white truncate">
                                {{ get_flag($fixture->home_team_code) }} {{ country_name($fixture->home_team_code, $fixture->home_team) }}
                            </span>
                            @if($fixture->isFinished())
                                <span class="shrink-0 scorebox text-xs">
                                    {{ $fixture->home_score }}-{{ $fixture->away_score }}
                                </span>
                            @else
                                <span class="shrink-0 text-white/35 text-xs">vs</span>
                            @endif
                            <span class="flex-1 text-sm font-semibold text-white truncate text-right">
                                {{ country_name($fixture->away_team_code, $fixture->away_team) }} {{ get_flag($fixture->away_team_code) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-xs text-white/45">{{ format_date_short($fixture->scheduled_at) }}</span>
                            <span class="pill {{ $fixture->status === 'FINISHED' ? 'pill-muted' : ($fixture->status === 'IN_PLAY' ? 'pill-live' : 'pill-info') }}">
                                {{ $fixture->status === 'FINISHED' ? 'Afgelopen' : ($fixture->status === 'IN_PLAY' ? '● Live' : 'Gepland') }}
                            </span>
                            <span class="text-xs text-white/40">👥 {{ $fixture->predictions_count }}</span>
                        </div>
                    </summary>

                    <div class="px-4 pb-4 pt-2 border-t border-white/8">
                        <form method="POST" action="/admin/match/{{ $fixture->id }}">
                            @csrf
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-3">
                                <div>
                                    <label class="label">Status</label>
                                    <select name="status" class="input">
                                        <option value="SCHEDULED" {{ $fixture->status === 'SCHEDULED' ? 'selected' : '' }}>Gepland</option>
                                        <option value="IN_PLAY" {{ $fixture->status === 'IN_PLAY' ? 'selected' : '' }}>Live</option>
                                        <option value="FINISHED" {{ $fixture->status === 'FINISHED' ? 'selected' : '' }}>Afgelopen</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="label">{{ country_name($fixture->home_team_code, $fixture->home_team) }} score</label>
                                    <input type="number" inputmode="numeric" name="home_score" min="0"
                                        value="{{ $fixture->home_score }}"
                                        class="input"
                                        placeholder="0">
                                </div>
                                <div>
                                    <label class="label">{{ country_name($fixture->away_team_code, $fixture->away_team) }} score</label>
                                    <input type="number" inputmode="numeric" name="away_score" min="0"
                                        value="{{ $fixture->away_score }}"
                                        class="input"
                                        placeholder="0">
                                </div>
                                <div>
                                    <label class="label">1e doelpunt (min.)</label>
                                    <input type="number" inputmode="numeric" name="first_goal_minute" min="1" max="120"
                                        value="{{ $fixture->first_goal_minute }}"
                                        class="input"
                                        placeholder="bv. 23">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-volt text-xs px-4 py-2">
                                ✓ Opslaan & punten berekenen
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
