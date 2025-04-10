@php use Carbon\Carbon; @endphp
@push('calendar-assets')
    @vite(['resources/css/calendar.css', 'resources/js/calendar.js'])
@endpush

<x-app-layout>

    @php
        $currentYear = Carbon::now()->year; // Текущий год
        $currentMonth = Carbon::now()->month; // Текущий месяц
    @endphp

    @livewire('calendar-month', ['year' => $currentYear, 'month' => $currentMonth])

</x-app-layout>
