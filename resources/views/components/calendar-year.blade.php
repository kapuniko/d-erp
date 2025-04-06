@php
    use App\Services\CalendarService;

    $months = [
        'Январь', 'Февраль', 'Март', 'Апрель',
        'Май', 'Июнь', 'Июль', 'Август',
        'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
    ];


@endphp

<div class="calendar-year">
    @for ($month = 1; $month <= 12; $month++)
        @php
            // Получаем сгруппированные события для текущего месяца и года
            $grouped = app(CalendarService::class)->getGroupedEvents($year, $month);

        @endphp

        <x-calendar-month
            :year="$year"
            :month="$month"
            :monthName="$months[$month - 1]"
            :grouped="$grouped"
        />
    @endfor
</div>

