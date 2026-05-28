<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren – FIFA 2026 Pool</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-green-900 via-green-800 to-green-900 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="text-6xl mb-3">⚽</div>
            <h1 class="text-3xl font-bold text-white">FIFA 2026 Pool</h1>
            <p class="text-green-300 mt-1">Doe mee met de pool</p>
        </div>

        @if(empty($code))
            <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
                <div class="text-4xl mb-3">🔒</div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Geen toegang</h2>
                <p class="text-gray-500 text-sm">
                    Je hebt een uitnodigingslink nodig om je te registreren.
                    Vraag de admin om een link.
                </p>
                <a href="/login" class="mt-4 block text-green-600 hover:underline text-sm">Terug naar inloggen</a>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-1">Account aanmaken</h2>
                <p class="text-sm text-green-600 mb-6">✅ Geldige uitnodigingslink</p>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="/register" class="space-y-4">
                    @csrf
                    <input type="hidden" name="invite_code" value="{{ $code }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Naam</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Jouw naam"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mailadres</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="jij@voorbeeld.nl"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Wachtwoord</label>
                        <input
                            type="password"
                            name="password"
                            required
                            minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Minimaal 6 tekens"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Wachtwoord bevestigen</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Herhaal wachtwoord"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition-colors"
                    >
                        Account aanmaken
                    </button>
                </form>

                <p class="text-center text-sm text-gray-600 mt-6">
                    Al een account?
                    <a href="/login" class="text-green-600 font-medium hover:underline">Inloggen</a>
                </p>
            </div>
        @endif
    </div>

</body>
</html>
