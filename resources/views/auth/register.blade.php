<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#060b08">
    <title>Registreren – FIFA 2026 Pool</title>
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md stagger">
        <div class="text-center mb-8">
            <div class="text-6xl mb-4 drop-shadow-[0_0_24px_rgba(189,240,59,0.4)]">⚽</div>
            <p class="kicker mb-1">Doe mee met de pool</p>
            <h1 class="h-display text-5xl">FIFA <span class="text-volt-400">2026</span></h1>
        </div>

        @if(empty($code))
            <div class="card p-8 text-center">
                <div class="text-4xl mb-3">🔒</div>
                <h2 class="font-display font-bold uppercase tracking-wide text-xl text-white mb-2">Geen toegang</h2>
                <p class="text-white/55 text-sm">
                    Je hebt een uitnodigingslink nodig om je te registreren.
                    Vraag de admin om een link.
                </p>
                <a href="/login" class="mt-4 block text-volt-400 hover:text-volt-300 text-sm">Terug naar inloggen</a>
            </div>
        @else
            <div class="card p-8">
                <h2 class="font-display font-bold uppercase tracking-wide text-xl text-white mb-1">Account aanmaken</h2>
                <p class="text-sm text-volt-400 mb-6">✓ Geldige uitnodigingslink</p>

                @if($errors->any())
                    <div class="alert alert-error mb-4">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="/register" class="space-y-4">
                    @csrf
                    <input type="hidden" name="invite_code" value="{{ $code }}">

                    <div>
                        <label class="label">Naam</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            class="input"
                            placeholder="Jouw naam"
                        >
                    </div>

                    <div>
                        <label class="label">E-mailadres</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="input"
                            placeholder="jij@voorbeeld.nl"
                        >
                    </div>

                    <div>
                        <label class="label">Wachtwoord</label>
                        <input
                            type="password"
                            name="password"
                            required
                            minlength="6"
                            class="input"
                            placeholder="Minimaal 6 tekens"
                        >
                    </div>

                    <div>
                        <label class="label">Wachtwoord bevestigen</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            class="input"
                            placeholder="Herhaal wachtwoord"
                        >
                    </div>

                    <button type="submit" class="btn btn-volt w-full py-3">
                        Account aanmaken
                    </button>
                </form>

                <p class="text-center text-sm text-white/50 mt-6">
                    Al een account?
                    <a href="/login" class="text-volt-400 font-medium hover:text-volt-300">Inloggen</a>
                </p>
            </div>
        @endif
    </div>

</body>
</html>
