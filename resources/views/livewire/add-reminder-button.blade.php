@php

    $reminderIcon = match($this->status) {
        'pending' => [
            'text' => 'Напоминалка создана на ' . $this->remindAt,
            'color' => 'text-green-500',
            'path' => 'M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5'
            ],
        'sent' => [
            'text' => 'Напоминалка отправлена',
            'color' => 'text-green-500',
            'path' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'
            ],
        default => [
            'text' => 'Добавить напоминалку (за 10 минут до начала)',
            'color' => 'text-gray-400',
            'path' => 'M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5'
            ],
    };
@endphp

<button wire:click="toggleReminder" class="@if($this->status === 'pending') opacity-100 @else opacity-0 @endif addReminder" title="{{ $reminderIcon['text'] }}">
    <svg xmlns="http://www.w3.org/2000/svg"
         fill="none"
         viewBox="0 0 24 24"
         stroke-width="1.5"
         stroke="currentColor"
         class="size-4 {{ $reminderIcon['color'] }}">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="{{ $reminderIcon['path'] }}" />
    </svg>
</button>

