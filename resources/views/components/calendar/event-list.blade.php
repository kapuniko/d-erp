@props(['title', 'events', 'data_attributes'])
@php
    use Carbon\Carbon;
@endphp

<div class="mb-5">
    <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">{{ $title }}</h4>
    <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
        @foreach($events as $event)
            <li>
                {{ $event->emoji }} {{ $event->name }}

                @switch($event->display_type->value)
                    @case('single')
                        <x-moonshine::badge color="gray">
                            {{ $event->event_time }}
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

                @if ($event->user_id === auth()->id())
                    <button class="edit-event-btn btn btn-sm btn-outline-secondary"
                            @foreach ($data_attributes as $attr)
                                data-{{ $attr }}="{{ $event->{$attr} }}"
                        @endforeach
                    >✏️</button>
                @endif
            </li>
        @endforeach
    </ul>
</div>
