@php
    $color = match($this->status) {
        'pending' => 'text-green-500',
        'sent' => 'text-yellow-500',
        default => 'text-gray-400',
    };
@endphp

<button wire:click="toggleReminder" class="@if($this->status === 'pending') opacity-100 @else opacity-0 @endif addReminder" title="Управление напоминанием">
    <svg xmlns="http://www.w3.org/2000/svg"
         fill="none"
         viewBox="0 0 24 24"
         stroke-width="1.5"
         stroke="currentColor"
         class="size-4 {{ $color }}">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
    </svg>
</button>
