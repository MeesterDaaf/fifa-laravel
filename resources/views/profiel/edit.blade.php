@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold text-gray-800 mb-6">👤 Mijn profiel</h1>

    {{-- Profiel bewerken --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="font-semibold text-gray-700 mb-4">Gegevens bewerken</h2>

        @if($errors->any() && ! session('show_delete'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="/profiel" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Naam</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-mailadres</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <hr class="border-gray-100">
            <p class="text-xs text-gray-400">Laat de wachtwoordvelden leeg als je je wachtwoord niet wilt wijzigen.</p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nieuw wachtwoord</label>
                <input type="password" name="password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500"
                    placeholder="Minimaal 6 tekens">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nieuw wachtwoord bevestigen</label>
                <input type="password" name="password_confirmation"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500"
                    placeholder="Herhaal wachtwoord">
            </div>

            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition-colors">
                💾 Profiel opslaan
            </button>
        </form>
    </div>

    {{-- Gevarenzone --}}
    @if($user->isLastAdmin())
        <div class="bg-amber-50 rounded-2xl border border-amber-200 p-6">
            <h2 class="font-semibold text-amber-800 mb-2">🔒 Account verwijderen niet mogelijk</h2>
            <p class="text-sm text-amber-700">
                Je bent de laatste beheerder. Maak eerst een andere deelnemer beheerder voordat je je eigen account kunt verwijderen.
            </p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-red-200 p-6">
            <h2 class="font-semibold text-red-700 mb-2">⚠️ Account verwijderen</h2>
            <p class="text-sm text-gray-600 mb-4">
                Dit verwijdert je account én al je voorspellingen definitief. Dit kan niet ongedaan worden gemaakt.
            </p>

            @if($errors->has('password'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
                    {{ $errors->first('password') }}
                </div>
            @endif

            <form method="POST" action="/profiel" onsubmit="return confirm('Weet je zeker dat je je account en al je voorspellingen permanent wilt verwijderen?');">
                @csrf
                @method('DELETE')
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bevestig met je wachtwoord</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Je huidige wachtwoord">
                </div>
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-xl transition-colors">
                    🗑️ Account definitief verwijderen
                </button>
            </form>
        </div>
    @endif

</div>
@endsection
