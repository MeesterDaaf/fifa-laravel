<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Player;
use App\Models\Prediction;
use App\Models\Team;
use App\Models\TournamentPrediction;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Voorspel-engine van de AI-bot.
 *  - Wedstrijduitslag: Elo → verwachte goals (Poisson) → meest waarschijnlijke score.
 *  - Toernooi: sterkste team als kampioen, topscorer uit een topland, kaarten o.b.v. gemiddelden.
 * De LLM (AiReasoningService) levert per voorspelling een korte onderbouwing (optioneel).
 */
class AiPredictionService
{
    private const GOALS_AVG = 1.35;   // gemiddeld aantal doelpunten per team
    private const YELLOW_PER_MATCH = 3.5;
    private const RED_PER_MATCH = 0.18;

    public function __construct(
        private AiReasoningService $reasoning,
        private ProbabilityService $probability,
    ) {}

    /** Doet alle openstaande voorspellingen. Geeft tellingen terug. */
    public function run(): array
    {
        $matches = $this->predictOpenMatches();
        $tournament = $this->predictTournament();

        return ['matches' => $matches, 'tournament' => $tournament];
    }

    /**
     * Vult de onderbouwing (ai_reasoning) aan voor bestaande bot-voorspellingen
     * die er nog geen hebben. Handig nadat de ANTHROPIC_API_KEY is ingesteld.
     * Geeft het aantal bijgewerkte voorspellingen terug.
     */
    public function backfillReasoning(?int $limit = null, bool $force = false): int
    {
        if (! $this->reasoning->enabled()) {
            return 0;
        }

        $bot = $this->bot();
        $count = 0;

        // Wedstrijdvoorspellingen (zonder onderbouwing, of alle bij --force).
        $query = Prediction::with('fixture')->where('user_id', $bot->id);
        if (! $force) {
            $query->whereNull('ai_reasoning');
        }
        if ($limit) {
            $query->limit($limit);
        }

        foreach ($query->get() as $pred) {
            if (! $pred->fixture) {
                continue;
            }
            $text = $this->reasoning->reason($this->matchPrompt($pred->fixture, [
                'home' => $pred->home_score,
                'away' => $pred->away_score,
            ]));
            if ($text) {
                $pred->update(['ai_reasoning' => $text]);
                $count++;
            }
        }

        // Toernooivoorspelling (zonder onderbouwing, of bij --force).
        if (! $limit || $count < $limit) {
            $tpQuery = TournamentPrediction::where('user_id', $bot->id);
            if (! $force) {
                $tpQuery->whereNull('ai_reasoning');
            }
            $tp = $tpQuery->first();
            if ($tp) {
                $text = $this->reasoning->reason($this->tournamentPrompt(
                    $tp->champion, $tp->top_scorer, $tp->total_yellow_cards, $tp->total_red_cards
                ));
                if ($text) {
                    $tp->update(['ai_reasoning' => $text]);
                    $count++;
                }
            }
        }

        return $count;
    }

    /** De bot-gebruiker (wordt aangemaakt als die nog niet bestaat). */
    public function bot(): User
    {
        return User::firstOrCreate(
            ['email' => 'ai-bot@local'],
            [
                'name'     => '🤖 Voorspel-AI',
                'is_bot'   => true,
                'is_admin' => false,
                'password' => bcrypt(Str::random(40)),
            ]
        );
    }

    /** Voorspelt alle nog-open wedstrijden zonder bestaande bot-voorspelling. */
    public function predictOpenMatches(): int
    {
        $bot = $this->bot();

        $alreadyDone = Prediction::where('user_id', $bot->id)->pluck('fixture_id');

        $fixtures = Fixture::openForPredictions()
            ->whereNotIn('id', $alreadyDone)
            ->get();

        $count = 0;
        foreach ($fixtures as $fixture) {
            // Onbekende teams (TBD, knock-out) overslaan tot ze bekend zijn.
            if (! $this->hasElo($fixture->home_team_code) || ! $this->hasElo($fixture->away_team_code)) {
                continue;
            }

            $score = $this->scoreline(
                $this->elo($fixture->home_team_code),
                $this->elo($fixture->away_team_code),
            );

            Prediction::create([
                'user_id'           => $bot->id,
                'fixture_id'        => $fixture->id,
                'home_score'        => $score['home'],
                'away_score'        => $score['away'],
                'first_goal_minute' => $score['first_goal_minute'],
                'ai_reasoning'      => $this->reasoning->reason($this->matchPrompt($fixture, $score)),
            ]);
            $count++;
        }

        return $count;
    }

    /** Vult de toernooivoorspelling van de bot als die nog ontbreekt. */
    public function predictTournament(): bool
    {
        $bot = $this->bot();
        $tp = TournamentPrediction::firstOrNew(['user_id' => $bot->id]);

        if (filled($tp->champion) && filled($tp->top_scorer) && $tp->total_yellow_cards !== null) {
            return false; // al gedaan
        }

        [$championTla, $championName] = $this->strongestTeam();
        $topScorer = $this->pickTopScorer();
        $matchCount = max(1, Fixture::count());
        $yellow = (int) round($matchCount * self::YELLOW_PER_MATCH);
        $red = (int) round($matchCount * self::RED_PER_MATCH);

        $tp->fill([
            'champion'           => $championName,
            'top_scorer'         => $topScorer,
            'total_yellow_cards' => $yellow,
            'total_red_cards'    => $red,
            'ai_reasoning'       => $this->reasoning->reason(
                $this->tournamentPrompt($championName, $topScorer, $yellow, $red)
            ),
        ])->save();

        return true;
    }

    // --- Model ---------------------------------------------------------------

    /**
     * Meest waarschijnlijke eindstand op basis van Elo → Poisson.
     *
     * @return array{home:int, away:int, first_goal_minute:int}
     */
    public function scoreline(int $eloHome, int $eloAway): array
    {
        ['home' => $lambdaHome, 'away' => $lambdaAway] = $this->lambdas($eloHome, $eloAway);

        // Argmax over alle scores 0..6 van het product van de twee Poisson-kansen.
        $bestHome = 0;
        $bestAway = 0;
        $bestProb = -1.0;
        for ($h = 0; $h <= 6; $h++) {
            for ($a = 0; $a <= 6; $a++) {
                $p = $this->poisson($h, $lambdaHome) * $this->poisson($a, $lambdaAway);
                if ($p > $bestProb) {
                    $bestProb = $p;
                    $bestHome = $h;
                    $bestAway = $a;
                }
            }
        }

        return ['home' => $bestHome, 'away' => $bestAway, 'first_goal_minute' => $this->goalMinute($lambdaHome, $lambdaAway)];
    }

    /** Verwachte doelpunten (λ) per team uit het Elo-verschil. */
    private function lambdas(int $eloHome, int $eloAway): array
    {
        $dr = $eloHome + config('elo.home_advantage') - $eloAway;
        $ratio = pow(10, $dr / 400);

        return [
            'home' => $this->clamp(self::GOALS_AVG * sqrt($ratio), 0.2, 4.5),
            'away' => $this->clamp(self::GOALS_AVG / sqrt($ratio), 0.2, 4.5),
        ];
    }

    private function goalMinute(float $lambdaHome, float $lambdaAway, int $jitter = 0): int
    {
        $minute = (int) round(90 / ($lambdaHome + $lambdaAway + 1)) + $jitter;

        return (int) $this->clamp($minute, 1, 90);
    }

    /**
     * Realistische maar gevarieerde eindstand: trekt uit de Poisson-verdeling
     * rond de Elo-verwachting i.p.v. altijd de meest waarschijnlijke score.
     * Gebruikt voor de "vul automatisch in"-knop, zodat niet iedereen identiek voorspelt.
     *
     * @return array{home:int, away:int, first_goal_minute:int}
     */
    public function variedScoreline(int $eloHome, int $eloAway): array
    {
        ['home' => $lambdaHome, 'away' => $lambdaAway] = $this->lambdas($eloHome, $eloAway);

        return [
            'home'              => min(7, $this->samplePoisson($lambdaHome)),
            'away'              => min(7, $this->samplePoisson($lambdaAway)),
            'first_goal_minute' => $this->goalMinute($lambdaHome, $lambdaAway, random_int(-8, 8)),
        ];
    }

    /** Trekt een willekeurig aantal doelpunten uit een Poisson(λ) (Knuth). */
    private function samplePoisson(float $lambda): int
    {
        $l = exp(-$lambda);
        $k = 0;
        $p = 1.0;
        do {
            $k++;
            $p *= mt_rand() / mt_getrandmax();
        } while ($p > $l);

        return $k - 1;
    }

    /**
     * Vult voor een gebruiker alle nog-open, nog-niet-voorspelde wedstrijden in
     * met een realistische (gevarieerde) Elo-suggestie. Bestaande voorspellingen
     * worden niet aangeraakt. Geeft het aantal ingevulde wedstrijden terug.
     */
    public function fillSuggestionsForUser(User $user): int
    {
        $predictedIds = Prediction::where('user_id', $user->id)->pluck('fixture_id');

        $fixtures = Fixture::openForPredictions()
            ->whereNotIn('id', $predictedIds)
            ->get();

        $count = 0;
        foreach ($fixtures as $fixture) {
            if (! $this->hasElo($fixture->home_team_code) || ! $this->hasElo($fixture->away_team_code)) {
                continue; // TBD / onbekende teams overslaan
            }

            $s = $this->variedScoreline(
                $this->elo($fixture->home_team_code),
                $this->elo($fixture->away_team_code),
            );

            Prediction::create([
                'user_id'           => $user->id,
                'fixture_id'        => $fixture->id,
                'home_score'        => $s['home'],
                'away_score'        => $s['away'],
                'first_goal_minute' => $s['first_goal_minute'],
            ]);
            $count++;
        }

        return $count;
    }

    private function poisson(int $k, float $lambda): float
    {
        return ($lambda ** $k) * exp(-$lambda) / $this->factorial($k);
    }

    private function factorial(int $k): float
    {
        $result = 1.0;
        for ($i = 2; $i <= $k; $i++) {
            $result *= $i;
        }
        return $result;
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    // --- Toernooi-keuzes -----------------------------------------------------

    /** @return array{0:?string,1:string} [tla, Nederlandse naam] van het sterkste team. */
    private function strongestTeam(): array
    {
        $best = null;
        $bestElo = -1;
        foreach (Team::all() as $team) {
            if ($this->hasElo($team->tla) && $this->elo($team->tla) > $bestElo) {
                $bestElo = $this->elo($team->tla);
                $best = $team;
            }
        }

        return $best
            ? [$best->tla, country_name($best->tla, $best->name)]
            : [null, 'Onbekend'];
    }

    private function pickTopScorer(): ?string
    {
        $topTeams = Team::has('players')->get()
            ->filter(fn ($t) => $this->hasElo($t->tla))
            ->sortByDesc(fn ($t) => $this->elo($t->tla))
            ->take(3);

        // Voorkeur: een echte spits (Centre-Forward) uit een topland.
        foreach ($topTeams as $team) {
            $cf = $team->players->first(fn (Player $p) => $p->position === 'Centre-Forward');
            if ($cf) {
                return $cf->name;
            }
        }
        // Anders: elke aanvaller uit een topland.
        foreach ($topTeams as $team) {
            $att = $team->players->first(fn (Player $p) => $p->positionGroup() === 'Aanvaller');
            if ($att) {
                return $att->name;
            }
        }

        return optional($topTeams->first()?->players->first())->name;
    }

    // --- Elo-helpers ---------------------------------------------------------

    private function elo(?string $code): int
    {
        return (int) config("elo.ratings.{$code}", config('elo.default'));
    }

    private function hasElo(?string $code): bool
    {
        return $code !== null && array_key_exists($code, config('elo.ratings'));
    }

    // --- Prompts -------------------------------------------------------------

    private function matchPrompt(Fixture $fixture, array $score): string
    {
        $home = country_name($fixture->home_team_code, $fixture->home_team);
        $away = country_name($fixture->away_team_code, $fixture->away_team);
        $eloHome = $this->elo($fixture->home_team_code);
        $eloAway = $this->elo($fixture->away_team_code);
        $p = $this->probability->forFixture($fixture);

        return "Onderbouw deze voorspelling op basis van uitsluitend onderstaande cijfers:\n"
            ."- Wedstrijd: {$home} vs {$away} ({$fixture->stageLabel()})\n"
            ."- Elo-rating: {$home} {$eloHome}, {$away} {$eloAway}\n"
            ."- Modelkansen: {$home} winst {$p['home']}%, gelijk {$p['draw']}%, {$away} winst {$p['away']}%\n"
            ."- Voorspelde uitslag: {$score['home']}-{$score['away']}\n"
            .'Schrijf één korte Nederlandse zin (max 25 woorden) die de uitslag verklaart vanuit het Elo-verschil en de kansen. Noem beide landen.';
    }

    private function tournamentPrompt(string $champion, ?string $topScorer, ?int $yellow, ?int $red): string
    {
        $matchCount = max(1, Fixture::count());

        // Feitelijke basis opzoeken bij de opgeslagen waarden.
        $championElo = null;
        foreach (Team::all() as $t) {
            if (country_name($t->tla, $t->name) === $champion) {
                $championElo = $this->elo($t->tla);
                break;
            }
        }

        $scorerContext = $topScorer ?? 'onbekend';
        if ($topScorer && $player = Player::where('name', $topScorer)->first()) {
            $team = $player->team;
            $teamName = $team ? country_name($team->tla, $team->name) : '';
            $scorerContext = trim("{$topScorer} ({$player->position}, {$teamName})");
        }

        return "Onderbouw deze toernooivoorspelling op basis van uitsluitend onderstaande feiten:\n"
            ."- Kampioen: {$champion}".($championElo ? " (hoogste Elo-rating: {$championElo})" : '')."\n"
            ."- Topscorer: {$scorerContext} — gekozen als aanvaller van een topland\n"
            ."- Gele kaarten: {$yellow} (≈ ".self::YELLOW_PER_MATCH." per wedstrijd × {$matchCount} wedstrijden)\n"
            ."- Rode kaarten: {$red} (≈ ".self::RED_PER_MATCH." per wedstrijd × {$matchCount} wedstrijden)\n"
            .'Schrijf één korte Nederlandse zin (max 35 woorden) die deze keuzes verklaart vanuit de ratings en de gemiddelden. Verzin geen extra feiten.';
    }
}
