<div class="emoji">
    {{ $event->emoji }}
    {{ \Carbon\Carbon::parse($event->event_time)->format('H:i') }}
    @if($event->amount !== null)
        {{ $event->amount >= 0 ? '💰' : '💸' }}{{ abs($event->amount) }}
    @endif
</div>
