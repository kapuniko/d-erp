{{--<!DOCTYPE html>--}}
{{--<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">--}}
{{--    <head>--}}
{{--        <meta charset="utf-8">--}}
{{--        <meta name="viewport" content="width=device-width, initial-scale=1">--}}

{{--        <title>D-ERP</title>--}}


{{--        <!-- Styles -->--}}
{{--        <style>--}}
{{--            html,--}}
{{--            body{--}}
{{--                background: black;--}}
{{--                margin: 0;--}}
{{--                padding: 0;--}}
{{--            }--}}

{{--            body{--}}
{{--                display:flex;--}}
{{--                flex-direction: column;--}}
{{--                align-items:center;--}}
{{--                justify-content: center;--}}
{{--                height:auto;--}}
{{--                min-height:100vh;--}}
{{--                animation: rotate 4s infinite ease-in-out;--}}
{{--            }--}}

{{--            .logo{--}}
{{--                width:300px;--}}
{{--                margin: 0;--}}
{{--                padding: 0;--}}
{{--                filter: drop-shadow(0px 0px 5px blue);--}}
{{--                animation: float 6s infinite ease-in-out;--}}
{{--                perspective: 1000px;--}}
{{--            }--}}

{{--            @keyframes float{--}}
{{--                0% {--}}
{{--                    transform:  translateY(0px) rotateX(5deg);--}}
{{--                }--}}
{{--                50%{--}}
{{--                    transform:  translateY(10px) rotateX(-5deg);--}}
{{--                }--}}
{{--                100% {--}}
{{--                    transform:  translateY(0px) rotateX(5deg);--}}
{{--                }--}}
{{--            }--}}

{{--            @keyframes rotate{--}}
{{--                0% {--}}
{{--                    transform:  rotateY(5deg);--}}
{{--                }--}}
{{--                50%{--}}
{{--                    transform:  rotateX(-5deg);--}}
{{--                }--}}
{{--                100% {--}}
{{--                    transform:  rotateY(5deg);--}}
{{--                }--}}
{{--            }--}}
{{--        </style>--}}
{{--    </head>--}}
{{--    <body>--}}

{{--        <img class="logo" src="{{ asset('images/d-erp.webp') }}" alt="D-ERP Logo">--}}

{{--    </body>--}}
{{--</html>--}}

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

