@component('mail::message')
# Hail Activity Detected

**{{ $eventCount }} hail event{{ $eventCount !== 1 ? 's' : '' }}** detected on **{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}**
within **{{ $radiusMiles }} miles** of your home base{{ $homeAddress ? ' (' . $homeAddress . ')' : '' }}.

@component('mail::table')
| Size | Location | Distance | Reports |
|:-----|:---------|:---------|:--------|
@foreach($events as $event)
| **{{ $event['max_size_inches'] }}"** ({{ $event['size_label'] }}) | {{ $event['location'] ?: '—' }} | {{ number_format($event['distance_miles'], 1) }} mi | {{ $event['report_count'] }} rpts |
@endforeach
@endcomponent

@component('mail::button', ['url' => $trackerUrl, 'color' => 'blue'])
View on Hail Tracker
@endcomponent

---

*Alert threshold: {{ $minSizeInches }}" minimum | Radius: {{ $radiusMiles }} mi | Cooldown: {{ $cooldownHours }} hrs*

To adjust your alert settings, visit **Settings → Hail Alerts** in PDR Shop Pro.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
