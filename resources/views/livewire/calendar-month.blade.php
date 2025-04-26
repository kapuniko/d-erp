@php
    use App\Enums\CalendarEventType;
    use Carbon\Carbon;

    $weekdays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    $firstDay = Carbon::create($year, $month, 1)->dayOfWeekIso - 1;
    $daysInMonth = Carbon::create($year, $month)->daysInMonth;

    $eventTypes = CalendarEventType::cases();

    $sampleCases = $artefactsCases->where('type', 'sample');
    $casesInCalendar = $artefactsCases->where('type', 'in_calendar');


//     TODO:
//     чтобы кроме эмодзи можно было сунуть ссылку на картинку - чтоб оно парсило
//     что это именно ссылка на картинку с w1.dawar.ru...gif|jpg и тд
//
//     обрезать названия на календаре (чтоб влазивало и не распидорсивало день на весь экран)
//
//     добавить ссылки на события (на двар-вику и офф)

@endphp
<x-moonshine::layout.grid
    @style('margin: 1.25rem')
                x-data="{   showModal: false, // Для кейсов
                formData: { type: 'in_calendar', date: null },
                showArtefactModal: false, // Для артефактов
                // openCaseModal метод остается здесь
                openCaseModal(type, date = null) {
                console.log('openCaseModal called with type:', type, 'date:', date); // Отладочный вывод
                    this.formData = { type: type, date: date };
                    this.showModal = true;
                    setTimeout(() => {
                         // Проверяем, что модалка действительно открыта перед диспатчем
                        if (this.showModal) {
                             console.log('Dispatching open-case-form-modal with:', this.formData); // Отладочный вывод
                             this.$dispatch('open-case-form-modal', this.formData);
                        }
                    }, 50); // Небольшая задержка
                },
                // Метод для открытия модалки артефактов (просто устанавливает переменную в true)
                openArtefactModal() {
                    console.log('openArtefactModal called');
                    this.showArtefactModal = true;
                },
              sidebarOpen: JSON.parse(localStorage.getItem('sidebarOpen') ?? 'true'),
                toggleSidebar() {
                  this.sidebarOpen = !this.sidebarOpen;
                  localStorage.setItem('sidebarOpen', JSON.stringify(this.sidebarOpen));
                },
              artefactId: null, // Используется для drop/remove
              artefactCount: null, // колличество для дропа
              artefactFromCaseId: null, // Используется для remove
              artefactIsDragging: false, // Для подсветки кейсов при перемещении артефактов

              // Обработчик события сброса кейса
                handleDroppedCase(detail) {

                     const { caseId, date } = detail; // Получаем ID кейса и дату из данных события

                     // Формируем имя рефа для CaseListComponent нужной даты
                     const targetRefName = `caseList_${date}`;

                     // <-- ДОБАВЛЕНО: Обертываем доступ к $refs в $nextTick -->
                     // Это откладывает поиск по $refs до тех пор, пока Alpine не завершит
                     // регистрацию всех рефов в обновленном DOM.
                     this.$nextTick(() => {
                         // Теперь пытаемся найти компонент через $refs
                         const targetComponentRef = this.$refs[targetRefName];

                         if (targetComponentRef) {
                             // Вызываем метод Livewire на целевом компоненте списка
                             targetComponentRef.$wire.addSampleCaseFromDrop(caseId);
                         } else {
                             console.error('Could not find target CaseListComponent via $refs', targetRefName, '. Final $refs state:', this.$refs);
                             // Опционально: показать сообщение об ошибке пользователю
                             // Например: $dispatch('notify', message: 'Не удалось добавить кейс. Попробуйте еще раз.', type: 'error');
                         }
                     }); // <-- Конец $nextTick

                },
            }"
    {{-- слушатель @case-added.window для закрытия модалки после сохранения --}}
    @case-added.window="showModal = false; $dispatch('close-case-form-modal');"

    {{-- <-- Слушатель для закрытия модалки артефактов --> --}}
    @close-artefact-modal.window="showArtefactModal = false;"

>

    <div :class="sidebarOpen ? 'col-span-12 xl:col-span-3' : 'xl:col-span-0 hidden'"
         class="relative"
    >
        <div x-data="{ tab: 'events' }" class="sticky top-0 ">
            <button @click="toggleSidebar" class="absolute top-0 right-0 p-1 text-xs z-10">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 9-3 3m0 0 3 3m-3-3h7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </button>

            <!-- Табы -->
            <div class="flex gap-2 mb-5">
                <button @click="tab = 'events'"
                        :class="{ 'text-indigo-400': tab === 'events' }"
                        class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700">
                    События
                </button>
                <button @click="tab = 'stuff'"
                        :class="{ 'text-indigo-400': tab === 'stuff' }"
                        class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700">
                    Всякие штуки
                </button>
            </div>

            <!-- Вкладка: Всякие штуки -->
            <div x-show="tab === 'stuff'" x-transition class="flex flex-col gap-4">
                <x-moonshine::layout.box title="Всякие штуки" class="dark:bg-gray-800">
                    <div class="flex flex-wrap gap-1">
                        @forelse($artefacts as $artefact)
                            <x-artefact.artefact :artefact="$artefact" :iconSize="25"/>
                        @empty
                            <li>Тут совсем ничиво нету =(</li>
                        @endforelse

                            <button class="rounded px-3 py-1 bg-indigo-600 text-white hover:bg-indigo-500"
                                    @click="openArtefactModal()"> {{-- Вызываем Alpine метод для открытия модалки --}}
                                + добавить какуюнибудь штуку
                            </button>
                    </div>
                </x-moonshine::layout.box>

                <x-moonshine::layout.box title="Чумаданы для всяких штук" class="dark:bg-gray-800">
                    <livewire:case-list-component
                        :listType="'sample'" {{-- Тип списка --}}
                    :key="'sample-cases-list'" {{-- Уникальный ключ для Livewire --}}
                    />

                    <button type="button"
                            class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-500" {{-- Tailwind классы --}}
                            {{-- Устанавливаем showModal в true и formData с типом 'sample' и date: null --}}
                            @click="openCaseModal('sample')">
                        + добавить чумадан
                    </button>

                </x-moonshine::layout.box>

            </div>

            <!-- Вкладка: События -->
            <div x-show="tab === 'events'" x-transition class="flex flex-col gap-4">
                @auth
                    <button id="addEventBtn" class="btn mb-3">
                        <x-moonshine::icon icon="plus" /> Добавить событие
                    </button>
                @endauth

                <x-calendar.event-list
                    :title="'1️⃣ Обычные (единичные)'"
                    :events="collect($monthlyEvents)->where('display_type.value', 'single')->unique('id')"
                    :data_attributes="['id', 'name', 'event_date', 'event_time', 'emoji', 'display_type', 'event_end_date']"
                    :dropdown_past="'single'"
                />

                <x-calendar.event-list
                    :title="'🔁 Повторяющиеся'"
                    :events="collect($monthlyEvents)->where('display_type.value', 'repeat')->unique('id')"
                    :data_attributes="['id', 'name', 'event_date', 'event_time', 'emoji', 'display_type', 'event_end_date', 'interval_hours', 'repeat_until']"
                />

                <x-calendar.event-list
                    :title="'🗓️ Многодневные'"
                    :events="collect($monthlyEvents)->where('display_type.value', 'range')->unique('id')"
                    :data_attributes="['id', 'name', 'event_date', 'event_time', 'emoji', 'display_type', 'event_end_date']"
                    :dropdown_past="'range'"
                />
            </div>
        </div>
    </div>

    <div :class="sidebarOpen ? 'col-span-12 xl:col-span-9' : 'xl:col-span-12'"
         class="relative"
    >
        <!-- Кнопка показать сайдбар (появляется, когда он скрыт) -->
        <button x-show="!sidebarOpen" @click="toggleSidebar" class="fixed top-20 left-1 z-10">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m12.75 15 3-3m0 0-3-3m3 3h-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>

        </button>
        <x-moonshine::layout.box>
            <div class="calendar-controls flex justify-between items-center mb-4">
                <button
                    wire:click="changeMonth('previous')"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600 text-sm"
                >
                    ← Предыдущий месяц
                </button>

                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ $monthName }} {{ $year }}</h2>

                <button
                    wire:click="changeMonth('next')"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600 text-sm"
                >
                    Следующий месяц →
                </button>
            </div>

            <div class="calendar">
                @foreach ($weekdays as $day)
                    <div class="day weekday dark:bg-gray-700">{{ $day }}</div>
                @endforeach

                @for ($i = 0; $i < $firstDay; $i++)
                    <div class="day empty dark:bg-gray-800"></div>
                @endfor

                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $key = sprintf("%04d-%02d-%02d", $year, $month, $day);
                        $today = Carbon::now();
                        $isToday = $today->year == $year && $today->month == $month && $today->day == $day;
                    @endphp

                    <div class="day bg-gray-800 {{ $isToday ? 'today' : '' }}  "
                         data-date="{{ $key }}"
                         x-data="{ isDragOver: false }"
                         @dragover.prevent="isDragOver = true" {{-- Позволяем сброс и меняем состояние --}}
                         @dragleave="isDragOver = false" {{-- Меняем состояние при уходе курсора --}}
                         @drop="isDragOver = false; Livewire.dispatch('add-sample-case-to-list-event', { sampleCaseId: event.dataTransfer.getData('case-id'), targetDate: $el.dataset.date })"
                         @dragend="isDragOver = false"
                         :class="{'bg-white bg-opacity-20': isDragOver}" {{-- Визуальный фидбек при наведении --}}
                        >
                        <div class="flex @if(Auth::user()) justify-between @else justify-center @endif w-full">
                            @if(Auth::user())<div class="size-6"></div>@endif
                            <span class="font-bold">{{ $day }}</span>

                            @if(Auth::user())
                                <x-dropdown>
                                    <x-slot name="trigger">
                                        <div class="addInDay">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </div>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-dropdown-link class="cursor-pointer" onclick="addEventInDay('{{ $key }}');">
                                            Добавить событие
                                        </x-dropdown-link>

                                        <x-dropdown-link class="cursor-pointer addCaseInDay"
                                                         @click="openCaseModal('in_calendar', '{{ $key }}')" >
                                            Добавить чумадан
                                        </x-dropdown-link>
                                    </x-slot>

                            </x-dropdown>
                            @endif
                        </div>

                        <div class="emoji-container">

                            @php
                                $events = ($grouped[$key] ?? collect())->sortBy('event_time');
                                $singleEvents = $events->whereIn('display_type.value', ['repeat', 'single']);
                                $rangeEvents = $events->where('display_type.value', 'range');
                                $nowCases = $casesInCalendar->where('calendar_date', $key)
                            @endphp

                            {{-- Повторяющиеся события --}}
                            @foreach($singleEvents as $event)
                                <x-calendar.event :event="$event" />
                            @endforeach

                            {{-- Многодневные события --}}
                            @foreach($rangeEvents as $event)
                                <x-calendar.event :event="$event" is_multiday="true" />
                            @endforeach


                            {{-- Вставляем компонент для отображения кейсов календаря для ЭТОГО дня --}}
                            <livewire:case-list-component
                                :listType="'in_calendar'" {{-- Тип списка --}}
                                :date="$key"           {{-- Передаем дату этого дня --}}
                                :key="'day-cases-list-' . $key" {{-- Уникальный ключ для Livewire --}}
                            />
                        </div>
                    </div>
                @endfor
            </div>
        </x-moonshine::layout.box>
    </div>


    @if(Auth::user())

        <!-- Модальное окно кейсов -->
        <div
            id="caseModal"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-black bg-opacity-50"
            x-show="showModal"
            style="display: none;"
            x-cloak
        >
            {{-- Устанавливаем @click.away и @click на фон ЗДЕСЬ, на внешнем контейнере модалки --}}
            <div
                class="fixed inset-0 bg-gray-800 bg-opacity-50 transition-opacity"
                aria-hidden="true"
                @click="showModal = false; $dispatch('close-case-form-modal');"
            ></div>

            <div
                class="flex items-center justify-center min-h-screen p-4"
                {{-- x-transition применяем к обертке контента --}}
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-90"
            >
                <div
                    class="relative bg-gray-800 rounded-lg shadow-xl max-w-lg mx-auto p-6"
                >
                    {{-- ... Заголовок ... --}}

                    <button
                        type="button"
                        class="absolute top-3 right-3 text-gray-400 hover:text-gray-500"
                        @click="showModal = false; $dispatch('close-case-form-modal');"
                    >
                        <span class="sr-only">Закрыть</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <livewire:case-form
                        x-ref="caseForm"
                    />

                </div>
            </div>
        </div>

        <!-- Модальное окно событий -->
        <div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 relative mx-auto mt-4">
                <!-- Кнопка закрытия -->
                <button id="closeModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">
                    &times;
                </button>

                <h2 class="text-xl font-semibold mb-4 text-center">Добавить событие</h2>

                <form id="eventForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="id" id="event_id">

                    <div>
                        <x-input-label for="event_display_type">Тип события</x-input-label>
                        <select name="display_type" id="event_display_type" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            @foreach ($eventTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->toString() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-1/4">
                            <x-input-label for="event_emoji">Эмодзи</x-input-label>
                            <input type="text" name="emoji" id="event_emoji"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>
                        <div class="w-3/4">
                            <x-input-label for="event_name">Название</x-input-label>
                            <input type="text" name="name" id="event_name" required
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>
                    </div>


                    <div>
                        <x-input-label for="event_date">Дата</x-input-label>
                        <input type="date" name="event_date" id="event_date" required
                               class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    </div>

                    <div>
                        <x-input-label for="event_time">Время</x-input-label>
                        <input type="time" name="event_time" id="event_time" required
                               class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    </div>

                    <div id="event_repeat_wrapper" class="hidden">
                        <div class="flex gap-4">
                            <div class="w-1/2">
                                <x-input-label for="event_interval_hours">Интервал (в часах)</x-input-label>
                                <input type="number" name="interval_hours" id="event_interval_hours"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            </div>
                            <div class="w-1/2">
                                <x-input-label for="event_repeat_until">Повторять до этой даты</x-input-label>
                                <input type="date" name="repeat_until" id="event_repeat_until"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div id="event_end_date_wrapper" class="hidden">
                        <x-input-label for="event_end_date">Дата окончания</x-input-label>
                        <input type="date" name="event_end_date" id="event_end_date"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    </div>

                    <div class="text-center">
                        <x-primary-button>
                            Сохранить
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- <-- Модальное окно для формы артефакта --> --}}
        <div
            id="artefactModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            x-show="showArtefactModal" {{-- Управляется новой Alpine переменной --}}
            style="display: none;"
            x-cloak
        >
            {{-- Фон модалки --}}
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"
                 @click="showArtefactModal = false; $dispatch('close-artefact-modal');"> {{-- Закрытие и диспатч события закрытия --}}
            </div>

            {{-- Контент модалки --}}
            <div class="flex items-center justify-center min-h-screen p-4"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg mx-auto p-6 dark:bg-gray-800">
                    {{-- Кнопка закрытия --}}
                    <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-500"
                            @click="showArtefactModal = false; $dispatch('close-artefact-modal');"> {{-- Закрытие и диспатч события закрытия --}}
                        <span class="sr-only">Закрыть</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    {{-- <-- ДОБАВЛЕНО: Livewire компонент формы артефакта --> --}}
                    <livewire:artefact-form
                        {{-- Слушатель события закрытия, чтобы сбросить форму --}}
                        x-on:close-artefact-modal.window="$wire.resetForm()"
                    />
                </div>
            </div>
        </div>

        {{-- ... Модальное окно событий (eventModal) ... --}}
    @endif

</x-moonshine::layout.grid>

@if(Auth::user())
    <script>

        document.getElementById('addEventBtn').addEventListener('click', () => {
            resetForm();
            showModal();
        });

        function addEventInDay(date) {
            resetForm();
            showModal();
            document.getElementById('event_date').value = date;
        }

        document.getElementById('closeModal').addEventListener('click', hideModal);

        function showModal() {
            document.getElementById('eventModal').style.display = 'block';
        }

        function hideModal() {
            document.getElementById('eventModal').style.display = 'none';
        }

        function resetForm() {
            document.getElementById('eventForm').reset();
            document.getElementById('event_id').value = '';

            // Сброс поля "Дата окончания"
            const endDateWrapper = document.getElementById('event_end_date_wrapper');
            const endDateInput = document.getElementById('event_end_date');
            const event_repeat_until = document.getElementById('event_repeat_until');
            const event_interval_hours = document.getElementById('event_interval_hours');

            const repeatWrapper = document.getElementById('event_repeat_wrapper');

            endDateWrapper.classList.add('hidden'); // скрыть блок
            repeatWrapper.classList.add('hidden'); // скрыть блок
            endDateInput.value = ''; // очистить значение
            event_repeat_until.value = ''; // очистить значение
            event_interval_hours.value = ''; // очистить значение
        }

        const eventTypeSelect = document.getElementById('event_display_type');
        const endDateWrapper = document.getElementById('event_end_date_wrapper');
        const repeatWrapper = document.getElementById('event_repeat_wrapper');

        function toggleEndDateField() {
            const selectedType = eventTypeSelect.value;
            if (selectedType === 'range') {
                endDateWrapper.classList.remove('hidden');
            } else {
                endDateWrapper.classList.add('hidden');
            }
        }

        function toggleRepeatFields() {
            const selectedType = eventTypeSelect.value;
            if (selectedType === 'repeat') {
                repeatWrapper.classList.remove('hidden');
            } else {
                repeatWrapper.classList.add('hidden');
            }
        }

        // при загрузке страницы
        toggleEndDateField();
        toggleRepeatFields();

        // при изменении типа события
        eventTypeSelect.addEventListener('change', () => {
            toggleEndDateField();
            toggleRepeatFields();
        });

        // Обработка клика на кнопку редактирования
        document.querySelectorAll('.edit-event-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                resetForm();
                document.getElementById('event_id').value = this.dataset.id;
                document.getElementById('event_name').value = this.dataset.name;
                document.getElementById('event_date').value = this.dataset.event_date;
                document.getElementById('event_time').value = this.dataset.event_time;
                document.getElementById('event_emoji').value = this.dataset.emoji;
                document.getElementById('event_display_type').value = this.dataset.display_type;
                if(this.dataset.display_type === 'range'){
                    document.getElementById('event_end_date_wrapper').classList.remove('hidden');
                    document.getElementById('event_end_date').value = this.dataset.event_end_date;
                }else{
                    document.getElementById('event_end_date').value = '';
                }
                if(this.dataset.display_type === 'repeat'){
                    document.getElementById('event_repeat_wrapper').classList.remove('hidden');
                    document.getElementById('event_interval_hours').value = this.dataset.interval_hours;
                    document.getElementById('event_repeat_until').value = this.dataset.repeat_until;
                }else{
                    document.getElementById('event_interval_hours').value = '';
                    document.getElementById('event_repeat_until').value = '';
                }
                showModal();
            });
        });

        // Отправка формы
        document.getElementById('eventForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/calendar-events/save', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                }
            }).then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Ошибка при сохранении');
                    }
                });
        });
    </script>
@endif


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

    window.addEventListener('calendar:updated', () => {

        setTimeout(() => {
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key.startsWith('event-visible-')) {
                    const id = key.replace('event-visible-', '');
                    const visible = localStorage.getItem(key) === 'true';
                    document.querySelectorAll(`.event_${id}`).forEach(div => {
                        div.classList.toggle('hidden', !visible);
                    });
                }
            }
        }, 30);
    });
</script>
