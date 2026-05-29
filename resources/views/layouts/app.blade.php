<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'FIFA 2026 Pool') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">

    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4">
            <div class="flex items-center justify-between h-14">
                <a href="/" class="font-bold text-green-700 text-lg">⚽ FIFA 2026</a>
                <div class="flex items-center gap-1 sm:gap-4">
                    <a href="/voorspellingen" class="text-sm text-gray-600 hover:text-green-700 transition-colors px-2 py-1">Wedstrijden</a>
                    <a href="/toernooi" class="text-sm text-gray-600 hover:text-green-700 transition-colors px-2 py-1">Toernooi</a>
                    <a href="/ranglijst" class="text-sm text-gray-600 hover:text-green-700 transition-colors px-2 py-1">Ranglijst</a>
                    <a href="/deelnemers" class="text-sm text-gray-600 hover:text-green-700 transition-colors px-2 py-1">Deelnemers</a>
                    @if(auth()->user()?->is_admin)
                        <a href="/admin" class="text-sm text-gray-600 hover:text-green-700 transition-colors px-2 py-1">Admin</a>
                    @endif
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-400 hover:text-red-500 transition-colors px-2 py-1 cursor-pointer">Uitloggen</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main>
        @if(session('success'))
            <div class="max-w-5xl mx-auto px-4 pt-4">
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-5xl mx-auto px-4 pt-4">
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>
