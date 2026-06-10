<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#060b08">
    <title>Inloggen – FIFA 2026 Pool</title>
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md stagger">
        <div class="text-center mb-8">
            <div class="text-6xl mb-4 drop-shadow-[0_0_24px_rgba(189,240,59,0.4)]">⚽</div>
            <p class="kicker mb-1">Voorspelpool · USA / Canada / Mexico</p>
            <h1 class="h-display text-5xl">FIFA <span class="text-volt-400">2026</span></h1>
        </div>

        <div class="card p-8">
            <h2 class="font-display font-bold uppercase tracking-wide text-xl text-white mb-6">Inloggen</h2>

            @if($errors->any())
                <div class="alert alert-error mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-ok mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="/login" class="space-y-4">
                @csrf

                <div>
                    <label class="label">E-mailadres</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="input"
                        placeholder="jij@voorbeeld.nl"
                    >
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label class="label">Wachtwoord</label>
                        <a href="/forgot-password" class="text-xs text-volt-400 hover:text-volt-300">Wachtwoord vergeten?</a>
                    </div>
                    <input
                        type="password"
                        name="password"
                        required
                        class="input"
                        placeholder="••••••••"
                    >
                </div>

                <button type="submit" class="btn btn-volt w-full py-3">
                    Inloggen
                </button>
            </form>

            <p class="text-center text-sm text-white/50 mt-6">
                Nog geen account?
                <a href="/register" class="text-volt-400 font-medium hover:text-volt-300">Registreer hier</a>
            </p>
        </div>
    </div>

</body>
</html>
