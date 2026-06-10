@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto px-4 py-6 stagger">

    <p class="kicker mb-1">Instellingen</p>
    <h1 class="h-display text-4xl mb-6">Mijn <span class="text-volt-400">profiel</span></h1>

    {{-- Profiel bewerken --}}
    <div class="card p-6 mb-6">
        <h2 class="font-display font-bold uppercase tracking-wide text-white mb-4">Gegevens bewerken</h2>

        @if($errors->any() && ! session('show_delete'))
            <div class="alert alert-error mb-4">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="/profiel" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="label">Naam</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="input">
            </div>

            <div>
                <label class="label">E-mailadres</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="input">
            </div>

            <hr class="border-white/8">
            <p class="text-xs text-white/40">Laat de wachtwoordvelden leeg als je je wachtwoord niet wilt wijzigen.</p>

            <div>
                <label class="label">Nieuw wachtwoord</label>
                <input type="password" name="password" class="input" placeholder="Minimaal 6 tekens">
            </div>

            <div>
                <label class="label">Nieuw wachtwoord bevestigen</label>
                <input type="password" name="password_confirmation" class="input" placeholder="Herhaal wachtwoord">
            </div>

            <button type="submit" class="btn btn-volt w-full py-3">
                💾 Profiel opslaan
            </button>
        </form>
    </div>

    {{-- Gevarenzone --}}
    @if($user->isLastAdmin())
        <div class="alert alert-warn p-6">
            <h2 class="font-display font-bold uppercase tracking-wide mb-2">🔒 Account verwijderen niet mogelijk</h2>
            <p class="text-sm text-white/65">
                Je bent de laatste beheerder. Maak eerst een andere deelnemer beheerder voordat je je eigen account kunt verwijderen.
            </p>
        </div>
    @else
        <div class="card p-6 border-signal-red/30!">
            <h2 class="font-display font-bold uppercase tracking-wide text-signal-red mb-2">⚠️ Account verwijderen</h2>
            <p class="text-sm text-white/55 mb-4">
                Dit verwijdert je account én al je voorspellingen definitief. Dit kan niet ongedaan worden gemaakt.
            </p>

            @if($errors->has('password'))
                <div class="alert alert-error mb-4">
                    {{ $errors->first('password') }}
                </div>
            @endif

            <form method="POST" action="/profiel" onsubmit="return confirm('Weet je zeker dat je je account en al je voorspellingen permanent wilt verwijderen?');">
                @csrf
                @method('DELETE')
                <div class="mb-3">
                    <label class="label">Bevestig met je wachtwoord</label>
                    <input type="password" name="password" required class="input" placeholder="Je huidige wachtwoord">
                </div>
                <button type="submit" class="btn btn-danger w-full py-3">
                    🗑️ Account definitief verwijderen
                </button>
            </form>
        </div>
    @endif

</div>
@endsection
