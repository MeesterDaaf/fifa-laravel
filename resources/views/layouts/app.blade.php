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
            <div class="flex items-center gap-3 h-14">
                <a href="/" class="font-bold text-green-700 text-lg shrink-0">⚽ FIFA 2026</a>

                {{-- Desktop-menu (verborgen op mobiel) --}}
                <div class="hidden sm:flex items-center gap-3 ml-auto">
                    <a href="/" class="text-sm text-gray-600 hover:text-green-700 transition-colors px-2 py-1">Dashboard</a>
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

                {{-- Reminder-bel (altijd zichtbaar) --}}
                <div class="shrink-0 ml-auto sm:ml-0">
                    @include('partials.reminder')
                </div>

                {{-- Mobiele actie-iconen: admin + uitloggen --}}
                <div class="flex sm:hidden items-center gap-1 shrink-0">
                    @if(auth()->user()?->is_admin)
                        <a href="/admin" class="px-2 py-1 text-gray-600 hover:text-green-700" title="Admin">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </a>
                    @endif
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-2 py-1 text-gray-600 hover:text-red-500 cursor-pointer" title="Uitloggen">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="pb-20 sm:pb-0">
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

    {{-- Mobiele onderbalk met de belangrijkste links (Heroicons) --}}
    @php
        $icons = [
            'home'      => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.5a.75.75 0 0 0 .75.75h4.5a.75.75 0 0 0 .75-.75V15a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v5.25a.75.75 0 0 0 .75.75h4.5a.75.75 0 0 0 .75-.75V9.75M8.25 21h8.25" />',
            'calendar'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />',
            'trophy'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />',
            'chart'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />',
            'users'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21 12.282 12.282 0 0 1 2.25 19.234v-.106c0-1.113.285-2.16.786-3.07M15 19.128a9.337 9.337 0 0 1-7.5 0m7.5 0c.621 0 1.125.504 1.125 1.125v.003M7.5 19.128a9.338 9.338 0 0 0-4.121-.952 4.125 4.125 0 0 1 7.533-2.493M7.5 19.128c0-1.113.285-2.16.786-3.07m0 0a4.125 4.125 0 0 1 7.428 0M12 9a3.75 3.75 0 1 0 0-7.5A3.75 3.75 0 0 0 12 9Z" />',
        ];
        $nav = [
            ['/', 'home', 'Dashboard', request()->path() === '/'],
            ['/voorspellingen', 'calendar', 'Wedstrijden', request()->is('voorspellingen*')],
            ['/toernooi', 'trophy', 'Toernooi', request()->is('toernooi*')],
            ['/ranglijst', 'chart', 'Ranglijst', request()->is('ranglijst*')],
            ['/deelnemers', 'users', 'Deelnemers', request()->is('deelnemers*')],
        ];
    @endphp
    <nav class="sm:hidden fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 z-50">
        <div class="grid grid-cols-5">
            @foreach($nav as [$url, $icon, $label, $active])
                <a href="{{ $url }}" class="flex flex-col items-center justify-center gap-1 py-2 transition-colors
                    {{ $active ? 'text-green-700' : 'text-gray-400' }}">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="{{ $active ? '2' : '1.5' }}" stroke="currentColor">
                        {!! $icons[$icon] !!}
                    </svg>
                    <span class="text-[10px] {{ $active ? 'font-semibold' : 'font-medium' }}">{{ $label }}</span>
                </a>
            @endforeach
        </div>
    </nav>

</body>
</html>
