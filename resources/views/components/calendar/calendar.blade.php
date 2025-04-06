@vite(['resources/css/calendar.css', 'resources/js/calendar.js'])

@php
    $months = [
        'Январь', 'Февраль', 'Март', 'Апрель',
        'Май', 'Июнь', 'Июль', 'Август',
        'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
    ];
    $weekdays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
@endphp

<div class="calendar-container">
    @for($month = 1; $month <= 12; $month++)
        <div class="month">
            <h2>{{ $months[$month - 1] }} 2025</h2>
            <div class="calendar">
                @foreach($weekdays as $day)
                    <div class="day weekday">{{ $day }}</div>
                @endforeach

                @php
                    $firstDay = \Carbon\Carbon::create(2025, $month, 1)->dayOfWeekIso - 1;
                    $daysInMonth = \Carbon\Carbon::create(2025, $month)->daysInMonth;
                    $grouped = app(\App\Services\CalendarService::class)->getGroupedEvents(); // Получаем все события
                @endphp

                @for($i = 0; $i < $firstDay; $i++)
                    <div class="day empty"></div>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $key = "$month-$day";
                        $today = \Carbon\Carbon::now();
                        $isToday = $today->year == 2025 && $today->month == $month && $today->day == $day;
                    @endphp
                    <div class="day {{ $isToday ? 'today' : '' }}">
                        <span>{{ $day }}</span>
                        <div class="emoji-container">
                            <!-- Повторяющиеся события -->
                            @foreach(($grouped[$key] ?? collect())->sortBy('event_time') as $event)
                                @if($event->display_type->value === 'repeat')
                                    <div>
                                        <!-- Повторяющееся событие -->
                                        <x-event :event="$event" />
                                    </div>
                                @endif
                            @endforeach

                            <!-- Разделитель -->
                            <hr>

                            <!-- Многодневные события -->
                            @foreach(($grouped[$key] ?? collect())->sortBy('event_time') as $event)
                                @if($event->display_type->value === 'range')
                                    <div>
                                        <!-- Многодневное событие -->
                                        <x-event :event="$event" is_multiday="true" />
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    @endfor
</div>
