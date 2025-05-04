<form wire:submit.prevent="store"
      x-on:open-case-form-modal.window="$wire.loadFormData($event.detail)"
      x-on:close-case-form-modal.window="$wire.resetForm()"
>
    {{-- Динамический заголовок формы --}}
    <h2 class="text-xl font-semibold mb-4">
        {{ $caseId ? 'Редактировать чумадан' : 'Создать новый чумадан' }}
    </h2>

    {{-- Поле для названия --}}
    <div>
        <label for="name">Название:</label>
        <input type="text" id="name" wire:model="name"
               class="mt-1 mb-4 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
        @error('name') <span>{{ $message }}</span> @enderror
    </div>

    {{-- Поле для типа (скрытое, управляется логикой открытия) --}}
    <input type="hidden" wire:model="type">

    {{-- Поля даты и времени - показываем только для типа 'in_calendar' --}}
    @if ($type === 'in_calendar')
        <div>
            <label for="calendar_date">Дата:</label>
            <input type="date" id="calendar_date" wire:model="calendar_date"
                   class="mt-1 mb-4 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            @error('calendar_date') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="calendar_time">Время:</label>
            <input type="time" id="calendar_time" wire:model="calendar_time"
                   class="mt-1 mb-4 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            @error('calendar_time') <span>{{ $message }}</span> @enderror
        </div>
    @endif

    @if ($type === 'sample')
    {{-- Другие поля формы типа sample --}}
    <div>
        <label for="sample_order">Sample Order:</label>
        <input type="number" id="sample_order" wire:model="sample_order"
               class="mt-1 mb-4 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
        @error('sample_order') <span>{{ $message }}</span> @enderror
    </div>
    @endif

    <div>
        <label for="case_profit" class="flex items-center"><img src="{{  asset('/images/m_game3.gif') }}" alt=""> Прибыль:</label>
        <input type="number" step="0.01" id="case_profit" wire:model="case_profit"
               class="mt-1 mb-4 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"> {{-- Используйте text для float --}}
        @error('case_profit') <span>{{ $message }}</span> @enderror
    </div>

    <div>
        <label for="case_description">Описание:</label>
        <textarea id="case_description" wire:model="case_description" rows="4"
        class="mt-1 mb-4 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
        @error('case_description') <span>{{ $message }}</span> @enderror
    </div>

    {{-- Кнопка сохранения --}}
    <x-primary-button> {{ $caseId ? 'Сохранить изменения' : 'Создать чумадан' }} </x-primary-button>
</form>
