<x-mail::message>
# Hoi {{ $user->name }} 👋

Je hebt de volgende wedstrijd{{ $matches->count() > 1 ? 'en' : '' }} van **{{ format_day($matchDate) }}** nog niet voorspeld:

<x-mail::table>
| Wedstrijd | Aftrap |
| :-------- | :----- |
@foreach($matches as $match)
| {{ country_name($match->home_team_code, $match->home_team) }} – {{ country_name($match->away_team_code, $match->away_team) }} | {{ to_nl_time($match->scheduled_at)->format('H:i') }} |
@endforeach
</x-mail::table>

Voorspel ze op tijd, anders mis je punten!

<x-mail::button :url="url('/voorspellingen')">
Maak je voorspelling
</x-mail::button>

Succes! 🍀<br>
{{ config('app.name') }}
</x-mail::message>
