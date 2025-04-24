
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


@php use Carbon\Carbon; @endphp
@push('calendar-assets')
    @vite(['resources/css/calendar.css', 'resources/js/calendar.js'])
@endpush

<x-app-layout>

    <x-moonshine::layout.grid @style('margin: 1.25rem')>
        <div class="col-span-12 xl:col-span-6 space-elements">
            <h2  class="pink_heading">
                Календарь событий:
            </h2>

        </div>

        <div class="col-span-12 xl:col-span-6 space-elements">
            @if(!Auth::user())
                <p>
                    Войдите, или зарегистрируйтесь, и вы сможете добавлять на календарь<br>
                    свои собственные штуки, за которыми хотели бы следить наглядно:<br>
                    события, откат предметов, квесты, что угодно =)
                    <span class="strelka">
                    <svg xmlns="http://www.w3.org/2000/svg" width="74" height="72" class="absolute right-0 tra sm:right-full lg:right-6 -top-4 lg:top-2 mr-4 lg:mr-0 rotate-[145deg] lg:rotate-0" fill="currentColor" viewBox="0 0 74 72">
                        <path d="M69.534 43.712a1 1 0 0 0-.71-1.223l-8.698-2.31a1 1 0 0 0-.514 1.934l7.733 2.052-2.052 7.733a1 1 0 0 0 1.933.512l2.308-8.698ZM1.087 21.218c5.689 7.084 15.88 16.735 28.015 22.695 12.15 5.966 26.39 8.29 39.968.407l-1.004-1.73c-12.796 7.429-26.285 5.321-38.082-.472-11.81-5.8-21.783-15.235-27.338-22.152l-1.56 1.252Z"></path>
                    </svg>
                </span>
                </p>

            @endif
        </div>

    </x-moonshine::layout.grid>


    @php
        $currentYear = Carbon::now()->year; // Текущий год
        $currentMonth = Carbon::now()->month; // Текущий месяц
    @endphp

    @livewire('calendar-month', ['year' => $currentYear, 'month' => $currentMonth])

</x-app-layout>

