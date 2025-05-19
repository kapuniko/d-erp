@props(['event', 'is_multiday' => false, 'reminderStatus' => 'none'])

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
    <div class="w-full flex align-center gap-1 justify-between emoji {{ 'event_'.$event->id }}" style="">
        <div class="size-4"></div>
        {{ $event->emoji }}
        {{ \Carbon\Carbon::parse($event->event_time)->format('H:i') }}
        @if($event->amount !== null)
            {{ $event->amount >= 0 ? '💰' : '💸' }}{{ abs($event->amount) }}
        @endif

    @if(Auth::user()->telegram_id)
        @livewire('add-reminder-button', [
            'calendarEventId' => $event->id,
            'remindAt' => \Carbon\Carbon::parse($event->calendar_datetime)->subMinutes(10)->format('Y-m-d H:i:s'),
            'eventKey' => $event->calendar_datetime,
            'emoji' => $event->emoji,
            'name' => $event->name,
            'initialStatus' => $reminderStatus,
            'eventTime' => $event->event_time,
        ], key($event->id . '|' . $event->calendar_datetime))

        @else
            <div class="size-4"></div>
        @endif

    </div>
@endif



