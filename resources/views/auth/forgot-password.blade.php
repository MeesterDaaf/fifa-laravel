<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#060b08">
    <title>Wachtwoord vergeten – FIFA 2026 Pool</title>
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md stagger">
        <div class="text-center mb-8">
            <div class="text-6xl mb-4 drop-shadow-[0_0_24px_rgba(189,240,59,0.4)]">⚽</div>
            <p class="kicker mb-1">Wachtwoord vergeten</p>
            <h1 class="h-display text-5xl">FIFA <span class="text-volt-400">2026</span></h1>
        </div>

        <div class="card p-8">
            <h2 class="font-display font-bold uppercase tracking-wide text-xl text-white mb-2">Wachtwoord resetten</h2>
            <p class="text-sm text-white/55 mb-6">Vul je e-mailadres in, dan sturen we je een link om een nieuw wachtwoord in te stellen.</p>

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

            <form method="POST" action="/forgot-password" class="space-y-4">
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

                <button type="submit" class="btn btn-volt w-full py-3">
                    Stuur resetlink
                </button>
            </form>

            <p class="text-center text-sm text-white/50 mt-6">
                <a href="/login" class="text-volt-400 font-medium hover:text-volt-300">← Terug naar inloggen</a>
            </p>
        </div>
    </div>

</body>
</html>
