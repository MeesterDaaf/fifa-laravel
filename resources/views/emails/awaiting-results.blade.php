<x-mail::message>
# Speelronde voorbij ⚽

Hoi {{ $admin->name }}, er {{ $matches->count() === 1 ? 'is' : 'zijn' }} **{{ $matches->count() }}** gespeelde wedstrijd{{ $matches->count() === 1 ? '' : 'en' }} die nog op invoer wacht{{ $matches->count() === 1 ? '' : 'en' }}:

<x-mail::table>
| Wedstrijd | Gespeeld |
| :-------- | :------- |
@foreach($matches as $m)
| {{ country_name($m->home_team_code, $m->home_team) }} – {{ country_name($m->away_team_code, $m->away_team) }} | {{ format_date($m->scheduled_at) }} |
@endforeach
</x-mail::table>

Tip: leg eerst de **ranglijst vast** en voer daarna de uitslagen in, dan kloppen de stijgers/dalers.

<x-mail::button :url="url('/admin')">
Uitslagen invoeren
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
