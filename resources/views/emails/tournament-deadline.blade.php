<x-mail::message>
# Hoi {{ $user->name }} 👋

Kleine maar belangrijke wijziging in de pool: de **toernooivoorspelling**
(winnaar, topscorer, totaal gele/rode kaarten) sluit voortaan automatisch
zodra de **eerste wedstrijd** van het toernooi begint.

@if($deadline)
**Deadline:** {{ format_day($deadline) }} om {{ to_nl_time($deadline)->format('H:i') }} 🔔

Daarna kun je je toernooivoorspelling niet meer wijzigen.
@else
Zodra de speelkalender bekend is, sluit de toernooivoorspelling bij de aftrap
van de eerste wedstrijd.
@endif

@if(count($missing) > 0)
⚠️ Je hebt nog niet alles ingevuld. Dit ontbreekt nog bij jou:

<x-mail::panel>
@foreach($missing as $item)
- {{ $item }}
@endforeach
</x-mail::panel>

Vul het op tijd in, anders mis je deze bonuspunten!
@else
✅ Jij hebt alle onderdelen al ingevuld — je hoeft niets te doen, behalve
eventueel je keuzes nog aanpassen vóór de deadline.
@endif

<x-mail::button :url="url('/toernooi')">
Naar mijn toernooivoorspelling
</x-mail::button>

Succes! 🍀<br>
{{ config('app.name') }}
</x-mail::message>
