@props(['event', 'is_multiday' => false])

<div class="emoji @if($is_multiday) multiday-event @endif">
    {{ $event->emoji }}
    {{ \Carbon\Carbon::parse($event->event_time)->format('H:i') }}
    @if($event->amount !== null)
        {{ $event->amount >= 0 ? '💰' : '💸' }}{{ abs($event->amount) }}
    @endif
    @if($is_multiday)
        <span class="event-duration">
            (Многодневное: {{ \Carbon\Carbon::parse($event->event_date)->format('d.m.Y') }} — {{ \Carbon\Carbon::parse($event->event_end_date)->format('d.m.Y') }})
        </span>
    @endif
</div>
