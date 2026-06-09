<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuw wachtwoord – FIFA 2026 Pool</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-green-900 via-green-800 to-green-900 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="text-6xl mb-3">⚽</div>
            <h1 class="text-3xl font-bold text-white">FIFA 2026 Pool</h1>
            <p class="text-green-300 mt-1">Nieuw wachtwoord instellen</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Kies een nieuw wachtwoord</h2>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/reset-password" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mailadres</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        required
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="jij@voorbeeld.nl"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nieuw wachtwoord</label>
                    <input
                        type="password"
                        name="password"
                        required
                        minlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="••••••••"
                    >
                    <p class="text-xs text-gray-400 mt-1">Minimaal 6 tekens.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Herhaal wachtwoord</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        minlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="••••••••"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition-colors"
                >
                    Wachtwoord opslaan
                </button>
            </form>

            <p class="text-center text-sm text-gray-600 mt-6">
                <a href="/login" class="text-green-600 font-medium hover:underline">← Terug naar inloggen</a>
            </p>
        </div>
    </div>

</body>
</html>
