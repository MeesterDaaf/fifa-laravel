<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#060b08">
    <title>Nieuw wachtwoord – FIFA 2026 Pool</title>
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md stagger">
        <div class="text-center mb-8">
            <div class="text-6xl mb-4 drop-shadow-[0_0_24px_rgba(189,240,59,0.4)]">⚽</div>
            <p class="kicker mb-1">Nieuw wachtwoord instellen</p>
            <h1 class="h-display text-5xl">FIFA <span class="text-volt-400">2026</span></h1>
        </div>

        <div class="card p-8">
            <h2 class="font-display font-bold uppercase tracking-wide text-xl text-white mb-6">Kies een nieuw wachtwoord</h2>

            @if($errors->any())
                <div class="alert alert-error mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/reset-password" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="label">E-mailadres</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        required
                        autofocus
                        class="input"
                        placeholder="jij@voorbeeld.nl"
                    >
                </div>

                <div>
                    <label class="label">Nieuw wachtwoord</label>
                    <input
                        type="password"
                        name="password"
                        required
                        minlength="6"
                        class="input"
                        placeholder="••••••••"
                    >
                    <p class="text-xs text-white/40 mt-1">Minimaal 6 tekens.</p>
                </div>

                <div>
                    <label class="label">Herhaal wachtwoord</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        minlength="6"
                        class="input"
                        placeholder="••••••••"
                    >
                </div>

                <button type="submit" class="btn btn-volt w-full py-3">
                    Wachtwoord opslaan
                </button>
            </form>

            <p class="text-center text-sm text-white/50 mt-6">
                <a href="/login" class="text-volt-400 font-medium hover:text-volt-300">← Terug naar inloggen</a>
            </p>
        </div>
    </div>

</body>
</html>
