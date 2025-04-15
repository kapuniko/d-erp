@props(['title', 'events', 'data_attributes', 'dropdown_past' => false])
@php
    use Carbon\Carbon;

   $now = Carbon::now();

   $upcomingEvents = collect($events);
   $pastEvents = collect();

   // Разделение только если включён dropdown
   if ($dropdown_past) {
       $upcomingEvents = collect($events)->filter(fn($e) => Carbon::parse($e->event_end_date)->gte($now));
       $pastEvents = collect($events)->filter(fn($e) => Carbon::parse($e->event_end_date)->lt($now));
   }
@endphp

<div class="mb-5" x-data="{ showPast: false }">
    <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">{{ $title }}</h4>

    {{-- Актуальные события --}}
    <ul class="eventList list-disc list-inside text-sm text-gray-700 dark:text-gray-300 space-y-1">
        @foreach($upcomingEvents as $event)
            @include('components.calendar._event-item', ['event' => $event, 'data_attributes' => $data_attributes])
        @endforeach
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


<script>
    function eyeToggle(eventId) {
        return {
            visible: true,
            storageKey: `event-visible-${eventId}`,

            init() {
                // Чтение состояния из localStorage
                const stored = localStorage.getItem(this.storageKey);
                this.visible = stored === null ? true : stored === 'true';

                // Применить это состояние к DOM
                this.applyVisibility();
            },

            toggle() {
                this.visible = !this.visible;

                // Сохранить новое состояние
                localStorage.setItem(this.storageKey, this.visible);

                // Применить
                this.applyVisibility();
            },

            applyVisibility() {
                const selector = `.event_${eventId}`;
                document.querySelectorAll(selector).forEach(div => {
                    if (this.visible) {
                        div.classList.remove('hidden');
                    } else {
                        div.classList.add('hidden');
                    }
                });
            }
        }
    }
</script>
