@props(['title', 'events', 'data_attributes', 'dropdown_past' => false])
@php
    use Carbon\Carbon;

   $now = Carbon::now()->toDateString();

   $upcomingEvents = collect($events);
   $pastEvents = collect();

   // Разделение только если включён dropdown
   if ($dropdown_past === 'single') {
       $upcomingEvents = collect($events)->filter(fn($e) => Carbon::parse($e->event_date)->gt($now));
       $pastEvents = collect($events)->filter(fn($e) => Carbon::parse($e->event_date)->lte($now));
   } elseif ($dropdown_past === 'range') {
       $upcomingEvents = collect($events)->filter(fn($e) => Carbon::parse($e->event_end_date)->gt($now));
       $pastEvents = collect($events)->filter(fn($e) => Carbon::parse($e->event_end_date)->lte($now));
   }
@endphp
<x-moonshine::layout.box title="{{ $title }}" class="dark:bg-gray-800" >
    <div x-data="{ showPast: false }">
        {{-- Актуальные события --}}
        <ul class="eventList list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
            @forelse($upcomingEvents as $event)
                @include('components.calendar._event-item', ['event' => $event, 'data_attributes' => $data_attributes])
            @empty
                <li>Событий нет</li>
            @endforelse
        </ul>

        {{-- Кнопка "показать прошедшие" --}}
        @if($dropdown_past && $pastEvents->isNotEmpty())
            <button class="text-xs mt-2 text-blue-600 hover:underline" @click="showPast = !showPast">
                <span x-show="!showPast">Показать прошедшие события</span>
                <span x-show="showPast">Скрыть прошедшие события</span>
            </button>

            {{-- Прошедшие события --}}
            <ul x-show="showPast" x-transition class="eventList list-disc list-inside text-sm text-gray-500 dark:text-gray-500 mt-2 space-y-1">
                @foreach($pastEvents as $event)
                    @include('components.calendar._event-item', ['event' => $event, 'data_attributes' => $data_attributes])
                @endforeach
            </ul>
        @endif
    </div>
</x-moonshine::layout.box>

