@props(['event', 'is_multiday' => false])

@if($is_multiday)
    <div class="emoji multiday-event {{ 'event_'.$event->id }}" style="">
        {{ $event->emoji }}
        @if($event->amount !== null)
            {{ $event->amount >= 0 ? '💰' : '💸' }}{{ abs($event->amount) }}
        @endif
        <span class="event-duration">
            {{ $event->name }}
        </span>
    </div>
@else
    <div class="emoji {{ 'event_'.$event->id }}" style="">
        {{ $event->emoji }}
        {{ \Carbon\Carbon::parse($event->event_time)->format('H:i') }}
        @if($event->amount !== null)
            {{ $event->amount >= 0 ? '💰' : '💸' }}{{ abs($event->amount) }}
        @endif
    </div>
@endif



