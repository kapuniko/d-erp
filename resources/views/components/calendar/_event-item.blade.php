@php
    use Carbon\Carbon;
@endphp

<li class="eventList_elem">
    {{ $event->emoji }} {{ $event->name }}

    @switch($event->display_type->value)
        @case('single')
            <x-moonshine::badge color="gray">
                {{ Carbon::parse($event->event_date)->translatedFormat('j F') }}  {{ $event->event_time }}
            </x-moonshine::badge>
            @break

        @case('repeat')
            <x-moonshine::badge color="gray">
                раз в {{ $event->interval_hours }} ч.
            </x-moonshine::badge>
            @break

        @case('range')
            <x-moonshine::badge color="gray">
                {{ Carbon::parse($event->event_date)->translatedFormat('j F') }}
                – {{ Carbon::parse($event->event_end_date)->translatedFormat('j F') }}
            </x-moonshine::badge>
            @break

        @default
            <x-moonshine::badge color="gray">
                Неизвестный тип события
            </x-moonshine::badge>
    @endswitch

    <!-- Тултип с кнопками -->
    <div x-data="{ open: false }" class="relative inline-block ml-2">
        <button @click="open = !open" class="text-gray-500 hover:text-black">
            ⋮
        </button>

        <div
            x-show="open"
            @click.away="open = false"
            x-transition
            class="absolute right-0 mt-1 z-50 bg-white border border-gray-200 rounded shadow-md p-2 flex gap-2"
        >
            <!-- Кнопка скрытия -->
            <button
                x-data="eyeToggle({{ $event->id }})"
                x-init="init()"
                @click="toggle(); open = false"
                class="text-gray-600 hover:text-black"
                title="Скрыть/Показать"
            >
                <template x-if="visible">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="size-4 transition duration-300">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.25 12C3.5 7.5 7.5 4.5 12 4.5s8.5 3
                                    9.75 7.5c-1.25 4.5-5.25 7.5-9.75
                                    7.5s-8.5-3-9.75-7.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </template>
                <template x-if="!visible">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="size-4 transition duration-300">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.98 8.223A10.477 10.477 0 0 0 1.934
                                12C3.226 16.338 7.244 19.5 12 19.5c.993 0
                                1.953-.138 2.863-.395M6.228 6.228A10.451
                                10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065
                                7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228
                                3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0
                                0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </template>
            </button>

            <!-- Кнопка редактирования -->
            @if ($event->user_id === auth()->id())
                <button
                    class="edit-event-btn text-gray-600 hover:text-black"
                    @click="open = false"
                    @foreach ($data_attributes as $attr)
                        data-{{ $attr }}="{{ $event->{$attr} }}"
                    @endforeach
                    title="Редактировать"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m16.862 4.487 1.687-1.688a1.875 1.875
                              0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897
                              1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863
                              4.487Zm0 0L19.5 7.125" />
                    </svg>
                </button>
            @endif
        </div>
    </div>
</li>

