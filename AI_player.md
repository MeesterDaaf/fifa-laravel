# AI-speler — ontwerp & stappenplan

> Status: **ontwerp, nog niet gebouwd.** Opgesteld om later op te pakken.
> Gekozen aanpak: **hybride** (statistisch model voor de cijfers + LLM voor de onderbouwing), **één** AI-speler.

## Doel

Een AI-deelnemer die net als een echte gebruiker meedoet in de pool: hij doet
wedstrijd- én toernooivoorspellingen, verschijnt op de ranglijst en deelnemers-
pagina, en scoort punten via dezelfde scoringslogica. De **cijfers** komen uit een
voetbalkundig model; een **LLM** voegt per voorspelling een korte Nederlandse
onderbouwing toe ("waarom deze uitslag").

## Waarom hybride

- Een taalmodel is statistisch niet beter in het voorspellen van "2-1" dan een
  Elo/Poisson-model. Het model doet dus de **getallen**.
- De LLM levert de **flavor**: een leuke, leesbare onderbouwing/“trash talk”.
- Valt de LLM (of API-key) weg, dan blijven de voorspellingen gewoon werken —
  alleen zonder onderbouwingstekst. Graceful degradation.

## Bestaande bouwstenen (al aanwezig in de app)

| Bouwsteen | Locatie | Gebruik voor de AI |
|---|---|---|
| Elo-ratings per land | `config/elo.php` | sterkte-inschatting per team |
| Win/gelijk/verlies-kansen | `app/Services/ProbabilityService.php` | basis voor uitslag + kampioen |
| Wedstrijden | `app/Models/Fixture.php` (`isOpen()`, `stage`) | welke wedstrijden voorspellen |
| Voorspellingen | `app/Models/Prediction.php` | waar de AI zijn uitslag opslaat |
| Toernooivoorspelling | `app/Models/TournamentPrediction.php` | kampioen/topscorer/kaarten |
| Teams + spelers (squads) | `app/Models/Team.php`, `Player.php` (incl. `positionGroup()`) | topscorer kiezen |
| Scoring | `app/Services/ScoringService.php` | AI scoort automatisch mee |

De AI hergebruikt dus de **bestaande** datamodellen en scoring — geen aparte
puntentelling nodig.

---

## Stappenplan

### Stap 1 — De AI als gebruiker

- Migratie: `users.is_bot` (boolean, default `false`).
- Eénmalig een bot-account aanmaken (seeder of `php artisan ai:create-bot`):
  - naam: bijv. **"🤖 Voorspel-AI"**, `is_admin = false`, `is_bot = true`.
  - `password = null` of een willekeurige hash; inloggen blokkeren (zie hieronder).
- Inloggen onmogelijk maken: in `AuthController@login` weigeren als `is_bot`
  (of simpelweg geen bruikbaar wachtwoord zetten).
- Uitsluiten van e-mailreminders: in `app/Services/ReminderService.php`
  `->where('is_bot', false)` toevoegen aan de `User::all()`-query.
- De bot heeft een geldig maar niet-bestaand e-mailadres nodig (bv.
  `bot@local`) — alleen om de `users.email`-unique constraint te vullen.

### Stap 2 — De voorspel-engine (`AiPredictionService`)

Nieuwe service `app/Services/AiPredictionService.php`.

**a) Wedstrijduitslag — Elo → Poisson**

```
HFA      = config('elo.home_advantage')          // bestaand thuisvoordeel
dr       = EloThuis + HFA - EloUit
ratio    = 10^(dr / 400)
mu       = 1.35                                   // gem. doelpunten per team (WK)
lambdaH  = clamp(mu * sqrt(ratio), 0.2, 4.5)
lambdaA  = clamp(mu / sqrt(ratio), 0.2, 4.5)
```

Meest waarschijnlijke eindstand = de combinatie (h, a) met h, a ∈ 0..6 die
`Poisson(h; lambdaH) * Poisson(a; lambdaA)` maximaliseert. (Onafhankelijke
Poisson → in de praktijk dicht bij `(round(lambdaH), round(lambdaA))`, maar de
expliciete argmax is netter en geeft betere gelijke spellen.)

**Eerste-doelpuntminuut** (optioneel, levert bonuspunten op):
`minuut ≈ round(90 / (lambdaH + lambdaA + 1))`, geklemd op 1..90. Veel
verwachte goals → vroeger eerste doelpunt.

**b) Toernooivoorspelling**

- **Kampioen:** team met de hoogste Elo-rating (eventueel licht gewogen met de
  groepsindeling). Simpel en verdedigbaar.
- **Topscorer:** kies een aanvaller (`Player::positionGroup() === 'Aanvaller'`,
  bv. een `Centre-Forward`) uit een van de 3 hoogst-gerate landen.
- **Gele/rode kaarten (totaal toernooi):**
  `aantal_wedstrijden * gem_per_wedstrijd`, met ~**3.5 geel** en ~**0.18 rood**
  per wedstrijd als startwaarde (later bij te stellen). Aantal wedstrijden =
  `Fixture::count()` of het bekende WK-2026-aantal (104).

### Stap 3 — LLM-onderbouwing (de "hybride" laag)

- Datamodel: kolom `predictions.ai_reasoning` (text, nullable) en
  `tournament_predictions.ai_reasoning` (text, nullable). Alleen de bot vult deze.
- Integratie: Anthropic Claude API (zie de **claude-api** skill voor SDK +
  prompt caching). Aanrader: een goedkoop, snel model (**Claude Haiku**) — het is
  maar een kort zinnetje per voorspelling.
- Env: `ANTHROPIC_API_KEY` in `.env`; config in `config/services.php`
  (`'anthropic' => ['key' => env('ANTHROPIC_API_KEY')]`).
- Prompt (schets): geef teams, Elo-context en de berekende uitslag mee; vraag
  **1–2 zinnen Nederlands** onderbouwing in een eigenwijze "pundit"-toon. Het
  model **verandert de cijfers niet** — die staan al vast vanuit Stap 2.
- **Fallback:** geen API-key of API-fout → `ai_reasoning` leeg laten, cijfers
  blijven staan. De feature mag nooit breken op de LLM.
- Kosten: ~104 wedstrijden één keer + toernooi = enkele centen. Verwaarloosbaar
  zolang we niet bij elke run opnieuw genereren (zie caching hieronder).

### Stap 4 — Trigger & timing (eerlijk t.o.v. echte spelers)

- Command: `php artisan ai:predict`
  - Loopt over alle **open** wedstrijden (`Fixture::isOpen()`) **zonder** bestaande
    bot-`Prediction`, en maakt er één.
  - Vult de toernooivoorspelling éénmalig als die nog leeg is.
  - Genereert `ai_reasoning` alleen bij het **aanmaken** (niet elke run opnieuw →
    bespaart LLM-kosten = caching).
- Automatisch via de bestaande cron/scheduler (`routes/console.php`), bv.
  dagelijks naast `reminders:send`. De AI voorspelt dus **vóór de aftrap**, net
  als iedereen — nooit als een wedstrijd al gesloten/bezig is.
- Admin-knop "🤖 Laat de AI voorspellen" in het admin-panel die hetzelfde
  command/servicepad aanroept (voor handmatig triggeren/testen).

### Stap 5 — Zichtbaarheid & fairness

- 🤖-badge naast de naam op **ranglijst** en **deelnemers** (check `is_bot`).
- De bot-voorspellingen zijn zichtbaar zoals bij iedereen (deelnemers-pagina);
  de `ai_reasoning` kan daar als citaat onder de voorspelling getoond worden.
- Eerlijkheid: alleen open wedstrijden, vóór deadline. Geen toegang tot
  uitslagen die echte spelers niet hebben.
- `getLeaderboard()` filtert nu op `is_admin = false`; de bot is non-admin en
  doet dus vanzelf mee. Eventueel apart markeren in tellingen ("X deelnemers +
  AI").

### Stap 6 — Test & validatie

- **Engine-unit:** sterk vs. zwak land → ruime overwinning; gelijkwaardig → krappe
  score/gelijkspel; uitkomst altijd binnen 0..6.
- **Toernooi:** kampioen = hoogste Elo; topscorer is een aanvaller uit een topland.
- **End-to-end:** `ai:predict` vult predictions voor open wedstrijden, bot
  verschijnt op de ranglijst, en na het invoeren van uitslagen scoort de bot
  punten via de bestaande `ScoringService`.
- **LLM-fallback:** zonder `ANTHROPIC_API_KEY` draait alles door, alleen zonder
  onderbouwing.

---

## Benodigde wijzigingen (samengevat)

**Migraties**
- `users.is_bot` (boolean, default false)
- `predictions.ai_reasoning` (text, nullable)
- `tournament_predictions.ai_reasoning` (text, nullable)

**Nieuw**
- `app/Services/AiPredictionService.php` (Elo→Poisson + toernooi + LLM-aanroep)
- `app/Console/Commands/AiPredict.php` (`ai:predict`)
- `app/Console/Commands/CreateAiBot.php` of een seeder (eenmalig bot aanmaken)
- evt. `app/Services/AiReasoningService.php` (Claude API, met fallback)

**Aanpassen**
- `routes/console.php` — `ai:predict` aan de scheduler
- `app/Services/ReminderService.php` — bots uitsluiten
- `app/Http/Controllers/AuthController.php` — login blokkeren voor bots
- `app/Http/Controllers/AdminController.php` + admin-view — knop "Laat de AI voorspellen"
- ranglijst- & deelnemers-views — 🤖-badge + onderbouwing tonen
- `config/services.php` + `.env` — `ANTHROPIC_API_KEY`

## Openstaande beslissingen (voor later)

1. **Model-keuze LLM:** Haiku (goedkoop, snel) vs. Sonnet (rijkere tekst).
2. **Toon van de onderbouwing:** zakelijk-analytisch of brutaal/“trash talk”.
3. **Kampioen-logica:** simpel hoogste Elo, of een lichte bracket-simulatie voor
   realistischere kansen.
4. **Eerste-doelpuntminuut:** wel/niet laten voorspellen (extra bonuskans).
5. **Eén bot** nu; later eventueel meerdere "karakters" (voorzichtig/gokker) door
   ruis op `mu`/λ te zetten.

## Globale inschatting

- Stap 1–2 (bot + statistische engine): de kern, het meeste werk, levert al een
  volledig functionele AI-speler.
- Stap 3 (LLM-laag): los bovenop te zetten; kan ook later toegevoegd worden
  zonder de cijfers te raken.
- Stap 4–6: integratie, knoppen, badges, tests.
