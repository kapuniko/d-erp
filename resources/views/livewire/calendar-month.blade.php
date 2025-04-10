@php
    use Carbon\Carbon;

    $weekdays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    $firstDay = Carbon::create($year, $month, 1)->dayOfWeekIso - 1;
    $daysInMonth = Carbon::create($year, $month)->daysInMonth;
@endphp
<x-moonshine::layout.grid @style('margin: 1.25rem')>
    <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3" >
        <x-moonshine::layout.box class="sticky top-0" title="События: {{ $monthName }}">

            {{-- Повторяющиеся --}}
            <div class="mb-5">
                <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">🔁 Повторяющиеся</h4>
                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    @foreach(collect($monthlyEvents)->where('display_type.value', 'repeat')->unique('name') as $event)
                        <li>{{ $event->emoji }} {{ $event->name }}</li>
                    @endforeach
                </ul>
            </div>

            {{-- Многодневные --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">🗓️ Многодневные</h4>
                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    @foreach(collect($monthlyEvents)->where('display_type.value', 'range')->unique('name') as $event)
                        <li>{{ $event->emoji }} {{ $event->name }}</li>
                    @endforeach
                </ul>
            </div>
        </x-moonshine::layout.box>

    </x-moonshine::layout.column>
    <x-moonshine::layout.column adaptiveColSpan="12" colSpan="9">
        <x-moonshine::layout.box>
            <div class="calendar-controls flex justify-between items-center mb-4">
                <button
                    wire:click="changeMonth('previous')"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600 text-sm"
                >
                    ← Предыдущий месяц
                </button>

                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ $monthName }} {{ $year }}</h2>

                <button
                    wire:click="changeMonth('next')"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600 text-sm"
                >
                    Следующий месяц →
                </button>
            </div>

            <div class="calendar">
                @foreach ($weekdays as $day)
                    <div class="day weekday font-semibold text-center">{{ $day }}</div>
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

                    <div class="day border rounded p-1 {{ $isToday ? 'today' : '' }}">
                        <span class="font-bold">{{ $day }}</span>
                        <div class="emoji-container">
                            {{-- Повторяющиеся события --}}
                            @foreach(($grouped[$key] ?? collect())->sortBy('event_time') as $event)
                                @if($event->display_type->value === 'repeat')
                                    <div>
                                        <x-calendar.event :event="$event" />
                                    </div>
                                @endif
                            @endforeach

                            <hr>

                            {{-- Многодневные события --}}
                            @foreach(($grouped[$key] ?? collect())->sortBy('event_time') as $event)
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
        </x-moonshine::layout.box>
    </x-moonshine::layout.column>
</x-moonshine::layout.grid>



