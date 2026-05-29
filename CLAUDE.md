# FIFA 2026 Pool — projectnotities

Laravel-app (Blade + Tailwind) voor een WK-2026 voorspelpool: gebruikers
voorspellen wedstrijduitslagen en toernooi-uitkomsten (winnaar, topscorer,
totaal gele/rode kaarten) en strijden op een ranglijst.

## Geplande features / roadmap

- **AI-speler** — een AI-deelnemer die meedoet en voorspellingen doet
  (hybride: Elo/Poisson voor de cijfers + LLM voor de onderbouwing). Volledig
  ontwerp + stappenplan staat in [`AI_player.md`](./AI_player.md). Nog niet
  gebouwd; klaar om op te pakken.

## Goed om te weten

- **Frontend assets:** voor mobiel/productie `npm run build` gebruiken (niet
  `npm run dev` — dat maakt een `public/hot` file die assets vanaf de
  dev-server laadt en op andere apparaten breekt).
- **E-mail:** SMTP via Brevo ingesteld in `.env` (`MAIL_*`). Reminders gaan via
  `php artisan reminders:send` en de scheduler (cron: `* * * * * php artisan schedule:run`).
- **Kansberekening & AI:** Elo-ratings in `config/elo.php`, kansen via
  `app/Services/ProbabilityService.php`.
