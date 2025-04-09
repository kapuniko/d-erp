@php use Carbon\Carbon; @endphp
@push('calendar-assets')
    @vite(['resources/css/calendar.css', 'resources/js/calendar.js'])
@endpush

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $currentYear = Carbon::now()->year; // Текущий год
        $currentMonth = Carbon::now()->month; // Текущий месяц
    @endphp

    @livewire('calendar-month', ['year' => $currentYear, 'month' => $currentMonth])

</x-app-layout>
