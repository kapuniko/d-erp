<form wire:submit.prevent="store"
      x-on:open-case-form-modal.window="$wire.loadFormData($event.detail)"
      x-on:close-case-form-modal.window="$wire.resetForm()"
>
    {{-- Поле для названия --}}
    <div>
        <label for="name">Название:</label>
        <input type="text" id="name" wire:model="name">
        @error('name') <span>{{ $message }}</span> @enderror
    </div>

    {{-- Поле для типа (можно скрыть или сделать readonly, если оно задается кнопкой открытия) --}}
    {{-- Или оставить его видимым, если пользователь может менять тип в форме --}}
    <input type="hidden" wire:model="type"> {{-- Чаще всего скрывают, т.к. тип задается кнопкой --}}
    @if($type === 'sample') <span>Тип: Шаблон</span> @endif
    @if($type === 'in_calendar') <span>Тип: В календаре</span> @endif


    {{-- Поля даты и времени - показываем только для типа 'in_calendar' --}}
    @if ($type === 'in_calendar')
        <div>
            <label for="calendar_date">Дата:</label>
            <input type="date" id="calendar_date" wire:model="calendar_date">
            @error('calendar_date') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="calendar_time">Время:</label>
            <input type="time" id="calendar_time" wire:model="calendar_time">
            @error('calendar_time') <span>{{ $message }}</span> @enderror
        </div>
    @endif

    {{-- Другие поля формы --}}
    <div>
        <label for="sample_order">Sample Order:</label>
        <input type="number" id="sample_order" wire:model="sample_order">
        @error('sample_order') <span>{{ $message }}</span> @enderror
    </div>
    <div>
        <label for="case_cost">Case Cost:</label>
        <input type="text" id="case_cost" wire:model="case_cost"> {{-- Используйте text для float --}}
        @error('case_cost') <span>{{ $message }}</span> @enderror
    </div>
    <div>
        <label for="case_profit">Case Profit:</label>
        <input type="text" id="case_profit" wire:model="case_profit"> {{-- Используйте text для float --}}
        @error('case_profit') <span>{{ $message }}</span> @enderror
    </div>


    {{-- Кнопка сохранения --}}
    <button type="submit">Сохранить</button>
</form>
