@php
    use Carbon\Carbon;

    $weekdays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    $firstDay = Carbon::create($year, $month, 1)->dayOfWeekIso - 1;
    $daysInMonth = Carbon::create($year, $month)->daysInMonth;
@endphp

<div class="month">
    <h2>{{ $monthName }} {{ $year }}</h2>

    <div class="calendar">
        @foreach ($weekdays as $day)
            <div class="day weekday">{{ $day }}</div>
        @endforeach

        @for ($i = 0; $i < $firstDay; $i++)
            <div class="day empty"></div>
        @endfor

        @for ($day = 1; $day <= $daysInMonth; $day++)
            @php
                $key = sprintf("%04d-%02d-%02d", $year, $month, $day);
                $today = Carbon::now();
                $isToday = $today->year == $year && $today->month == $month && $today->day == $day;
            @endphp

            <div class="day {{ $isToday ? 'today' : '' }}">
                <span>{{ $day }}</span>
                <div class="emoji-container">
                    {{-- Повторяющиеся события --}}
                    @foreach(($grouped[$key] ?? collect())->sortBy('event_time') as $event)
                        @if($event->display_type->value === 'repeat')
                            <div>
                                <x-event :event="$event" />
                            </div>
                        @endif
                    @endforeach

                    <hr>

                    {{-- Многодневные события --}}
                    @foreach(($grouped[$key] ?? collect())->sortBy('event_time') as $event)
                        @if($event->display_type->value === 'range')
                            <div>
                                <x-event :event="$event" is_multiday="true" />
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endfor
    </div>
</div>
