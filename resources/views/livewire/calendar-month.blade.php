@php use Carbon\Carbon; @endphp
<div class="month">


    <div class="calendar-controls flex justify-center gap-4 my-4">
        <button
            wire:click="changeMonth('previous')"
            class="text-gray-600 font-semibold py-2 px-4 rounded-lg shadow transition duration-200"
        >
            ← Предыдущий месяц
        </button>

        <h2>{{ $monthName }} {{ $year }}</h2>

        <button
            wire:click="changeMonth('next')"
            class="text-gray-600 font-semibold py-2 px-4 rounded-lg shadow transition duration-200"
        >
            Следующий месяц →
        </button>
    </div>

    <div class="calendar">
        @foreach (['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'] as $day)
            <div class="day weekday">{{ $day }}</div>
        @endforeach

        @php
            $firstDay = Carbon::create($year, $month, 1)->dayOfWeekIso - 1;
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        @endphp

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
                    @foreach(($groupedEvents[$key] ?? collect())->sortBy('event_time') as $event)
                        @if($event->display_type->value === 'repeat')
                            <div>
                                <x-calendar.event :event="$event" />
                            </div>
                        @endif
                    @endforeach

                    <hr>

                    {{-- Многодневные события --}}
                    @foreach(($groupedEvents[$key] ?? collect())->sortBy('event_time') as $event)
                        @if($event->display_type->value === 'range')
                            <div>
                                <x-calendar.event :event="$event" is_multiday="true" />
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endfor
    </div>
</div>
