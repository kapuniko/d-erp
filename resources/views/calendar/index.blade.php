@vite(['resources/css/calendar.css', 'resources/js/calendar.js'])
@php
    $year = 2025;
    $month = 5  ;
    $months = [
        'Январь', 'Февраль', 'Март', 'Апрель',
        'Май', 'Июнь', 'Июль', 'Август',
        'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
    ];
@endphp
<x-calendar-month
    :year="$year"
    :month="$month"
    :month-name="$months[$month - 1]"
    :grouped="$grouped"
/>


