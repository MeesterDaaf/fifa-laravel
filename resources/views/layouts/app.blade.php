<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#060b08">
    <title>{{ config('app.name', 'FIFA 2026 Pool') }}</title>
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">

    <nav class="bg-pitch-950/85 backdrop-blur-md border-b border-white/10 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4">
            <div class="flex items-center gap-3 h-14">
                <a href="/" class="shrink-0 flex items-baseline gap-1.5 font-display font-extrabold italic uppercase text-lg leading-none">
                    <span class="text-white">FIFA</span><span class="text-volt-500">2026</span>
                    <span class="not-italic text-[10px] font-bold tracking-[0.3em] text-white/40">POOL</span>
                </a>

                {{-- Desktop-menu (verborgen op mobiel) --}}
                @php
                    $topNav = [
                        ['/', 'Dashboard', request()->path() === '/'],
                        ['/voorspellingen', 'Wedstrijden', request()->is('voorspellingen*')],
                        ['/toernooi', 'Toernooi', request()->is('toernooi*')],
                        ['/groepen', 'Groepen', request()->is('groepen*')],
                        ['/ranglijst', 'Ranglijst', request()->is('ranglijst*')],
                        ['/deelnemers', 'Deelnemers', request()->is('deelnemers*')],
                    ];
                @endphp
                <div class="hidden sm:flex items-center gap-1 ml-auto">
                    @foreach($topNav as [$url, $label, $active])
                        <a href="{{ $url }}" class="navlink {{ $active ? 'navlink-active' : '' }}">{{ $label }}</a>
                    @endforeach
                    @if($whatsappGroupUrl ?? null)
                        <a href="{{ $whatsappGroupUrl }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1.5 text-sm font-medium text-[#25D366] hover:text-[#3ee07c] transition-colors px-2 py-1" title="WhatsApp-groep">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.149-.197.297-.767.967-.94 1.164-.173.198-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.247-.694.247-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.885-9.885 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.359.101 11.892c0 2.096.549 4.142 1.595 5.945L0 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.582 0 11.94-5.359 11.943-11.893a11.821 11.821 0 00-3.416-8.452z"/></svg>
                        </a>
                    @endif
                    @if(auth()->user()?->is_admin)
                        <a href="/admin" class="navlink {{ request()->is('admin*') ? 'navlink-active' : '' }}">Admin</a>
                    @endif
                    <a href="/profiel" class="navlink {{ request()->is('profiel*') ? 'navlink-active' : '' }}">Profiel</a>
                    <button type="button" onclick="toggleTheme()" class="navlink cursor-pointer" title="Wissel licht/donker thema">
                        <svg class="w-5 h-5 only-dark" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                        <svg class="w-5 h-5 only-light" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                    </button>
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="navlink hover:text-signal-red cursor-pointer">Uitloggen</button>
                    </form>
                </div>

                {{-- Reminder-bel (altijd zichtbaar) --}}
                <div class="shrink-0 ml-auto sm:ml-0">
                    @include('partials.reminder')
                </div>

                {{-- Mobiele actie-iconen: whatsapp + profiel + admin + uitloggen --}}
                <div class="flex sm:hidden items-center gap-1 shrink-0">
                    @if($whatsappGroupUrl ?? null)
                        <a href="{{ $whatsappGroupUrl }}" target="_blank" rel="noopener" class="px-2 py-1 text-[#25D366]" title="WhatsApp-groep">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.149-.197.297-.767.967-.94 1.164-.173.198-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.247-.694.247-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.885-9.885 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.359.101 11.892c0 2.096.549 4.142 1.595 5.945L0 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.582 0 11.94-5.359 11.943-11.893a11.821 11.821 0 00-3.416-8.452z"/></svg>
                        </a>
                    @endif
                    <button type="button" onclick="toggleTheme()" class="px-2 py-1 text-white/60 hover:text-volt-400 cursor-pointer" title="Wissel licht/donker thema">
                        <svg class="w-6 h-6 only-dark" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                        <svg class="w-6 h-6 only-light" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                    </button>
                    <a href="/profiel" class="px-2 py-1 text-white/60 hover:text-volt-400" title="Profiel">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </a>
                    @if(auth()->user()?->is_admin)
                        <a href="/admin" class="px-2 py-1 text-white/60 hover:text-volt-400" title="Admin">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </a>
                    @endif
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-2 py-1 text-white/60 hover:text-signal-red cursor-pointer" title="Uitloggen">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        {{-- volt-streep onder de balk --}}
        <div class="h-px bg-gradient-to-r from-transparent via-volt-500/50 to-transparent"></div>
    </nav>

    <main class="pb-24 sm:pb-10">
        @if(session('success'))
            <div class="max-w-5xl mx-auto px-4 pt-4 reveal">
                <div class="alert alert-ok">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-5xl mx-auto px-4 pt-4 reveal">
                <div class="alert alert-error">{{ session('error') }}</div>
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
            'groups'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />',
        ];
        $nav = [
            ['/', 'home', 'Dashboard', request()->path() === '/'],
            ['/voorspellingen', 'calendar', 'Wedstr.', request()->is('voorspellingen*')],
            ['/toernooi', 'trophy', 'Toernooi', request()->is('toernooi*')],
            ['/groepen', 'groups', 'Groepen', request()->is('groepen*')],
            ['/ranglijst', 'chart', 'Stand', request()->is('ranglijst*')],
            ['/deelnemers', 'users', 'Spelers', request()->is('deelnemers*')],
        ];
    @endphp
    <nav class="sm:hidden fixed bottom-0 inset-x-0 bg-pitch-950/90 backdrop-blur-md border-t border-white/10 z-50">
        <div class="grid grid-cols-6">
            @foreach($nav as [$url, $icon, $label, $active])
                <a href="{{ $url }}" class="relative flex flex-col items-center justify-center gap-1 py-2 transition-colors
                    {{ $active ? 'text-volt-400' : 'text-white/40' }}">
                    @if($active)
                        <span class="absolute top-0 inset-x-3 h-0.5 rounded-full bg-volt-500 shadow-[0_0_8px_rgba(189,240,59,0.8)]"></span>
                    @endif
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="{{ $active ? '2' : '1.5' }}" stroke="currentColor">
                        {!! $icons[$icon] !!}
                    </svg>
                    <span class="text-[10px] truncate max-w-full font-display uppercase tracking-wider {{ $active ? 'font-bold' : 'font-medium' }}">{{ $label }}</span>
                </a>
            @endforeach
        </div>
    </nav>

</body>
</html>
