@php
    use App\Enums\CalendarEventType;
    use Carbon\Carbon;

    $weekdays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    $firstDay = Carbon::create($year, $month, 1)->dayOfWeekIso - 1;
    $daysInMonth = Carbon::create($year, $month)->daysInMonth;

    $eventTypes = CalendarEventType::cases();

//     TODO:
//     чтобы кроме эмодзи можно было сунуть ссылку на картинку - чтоб оно парсило
//     что это именно ссылка на картинку с w1.dawar.ru...gif|jpg и тд
//
//     обрезать названия на календаре (чтоб влазивало и не распидорсивало день на весь экран)
//
//     вывести даты в сайдбаре
//
//     сделать бледными события которые уже прошли
//     добавить ссылки на события (на двар-вику и офф)

@endphp
<x-moonshine::layout.grid @style('margin: 1.25rem')>
    <x-moonshine::layout.column adaptiveColSpan="12" colSpan="3">
        <x-moonshine::layout.box class="sticky top-0" title="События: {{ $monthName }}">

            {{-- Обычные --}}
            <x-calendar.event-list
                :title="'1️⃣ Обычные (единичные)'"
                :events="collect($monthlyEvents)->where('display_type.value', 'single')->unique('id')"
                :data_attributes="['id', 'name', 'event_date', 'event_time', 'emoji', 'display_type', 'event_end_date']"
            />

            {{-- Повторяющиеся --}}
            <x-calendar.event-list
                :title="'🔁 Повторяющиеся'"
                :events="collect($monthlyEvents)->where('display_type.value', 'repeat')->unique('id')"
                :data_attributes="['id', 'name', 'event_date', 'event_time',
                                   'emoji', 'display_type', 'event_end_date',
                                   'interval_hours', 'repeat_until']"
            />

            {{-- Многодневные --}}
            <x-calendar.event-list
                :title="'🗓️ Многодневные'"
                :events="collect($monthlyEvents)->where('display_type.value', 'range')->unique('id')"
                :data_attributes="['id', 'name', 'event_date', 'event_time',
                                   'emoji', 'display_type', 'event_end_date']"
                :dropdown_past="true"
            />

            <!-- Кнопка для добавления -->
            @auth
                <button id="addEventBtn" class="btn mb-3"><x-moonshine::icon icon="plus" /> Добавить событие</button>
            @endauth


        </x-moonshine::layout.box>

    </x-moonshine::layout.column>


    <x-moonshine::layout.column adaptiveColSpan="12" colSpan="9">
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
                    <div class="day weekday font-semibold text-center">{{ $day }}</div>
                @endforeach

                @for ($i = 0; $i < $firstDay; $i++)
                    <div class="day empty"></div>
                @endfor

                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $key = sprintf("%04d-%02d-%02d", $year, $month, $day);
                        $today = Carbon::now();
                        $isToday = $today->year == $year && $today->month == $month && $today->day == $day;
                    @endphp

                    <div class="day border rounded p-1 {{ $isToday ? 'today' : '' }}">
                        <span class="font-bold">{{ $day }}</span>
                        <div class="emoji-container">

                            @php
                                $events = ($grouped[$key] ?? collect())->sortBy('event_time');
                                $singleEvents = $events->whereIn('display_type.value', ['repeat', 'single']);
                                $rangeEvents = $events->where('display_type.value', 'range');
                            @endphp

                            {{-- Повторяющиеся события --}}
                            @foreach($singleEvents as $event)
                                <x-calendar.event :event="$event" />
                            @endforeach

                            <hr>
                            {{-- Многодневные события --}}
                            @foreach($rangeEvents as $event)
                                <x-calendar.event :event="$event" is_multiday="true" />
                            @endforeach

                        </div>
                    </div>
                @endfor
            </div>
        </x-moonshine::layout.box>
    </x-moonshine::layout.column>

    <!-- Модальное окно -->
    <div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative mx-auto mt-4">
            <!-- Кнопка закрытия -->
            <button id="closeModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">
                &times;
            </button>

            <h2 class="text-xl font-semibold mb-4 text-center">Добавить событие</h2>

            <form id="eventForm" class="space-y-4">
                @csrf
                <input type="hidden" name="id" id="event_id">

                <div>
                    <label for="event_display_type" class="block text-sm font-medium text-gray-700">Тип события</label>
                    <select name="display_type" id="event_display_type" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach ($eventTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->toString() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-4">
                    <div class="w-1/4">
                        <label for="event_emoji" class="block text-sm font-medium text-gray-700">Эмодзи</label>
                        <input type="text" name="emoji" id="event_emoji"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="w-3/4">
                        <label for="event_name" class="block text-sm font-medium text-gray-700">Название</label>
                        <input type="text" name="name" id="event_name" required
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>


                <div>
                    <label for="event_date" class="block text-sm font-medium text-gray-700">Дата</label>
                    <input type="date" name="event_date" id="event_date" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="event_time" class="block text-sm font-medium text-gray-700">Время</label>
                    <input type="time" name="event_time" id="event_time" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div id="event_repeat_wrapper" class="hidden">
                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label for="event_interval_hours" class="block text-sm font-medium text-gray-700">Интервал (в часах)</label>
                            <input type="number" name="interval_hours" id="event_interval_hours"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="w-1/2">
                            <label for="event_repeat_until" class="block text-sm font-medium text-gray-700">Повторять до этой даты</label>
                            <input type="date" name="repeat_until" id="event_repeat_until"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <div id="event_end_date_wrapper" class="hidden">
                    <label for="event_end_date" class="block text-sm font-medium text-gray-700">Дата окончания</label>
                    <input type="date" name="event_end_date" id="event_end_date"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="text-center">
                    <button type="submit"
                            class="mt-4 inline-flex items-center justify-center px-4 py-2  text-sm font-medium rounded-lg shadow  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-moonshine::layout.grid>

<script>

    document.getElementById('addEventBtn').addEventListener('click', () => {
        resetForm();
        showModal();
    });

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
