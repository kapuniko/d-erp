<div>
    @forelse($cases as $case)
        {{-- Используем ваш существующий компонент artefacts-case для отображения каждого элемента --}}
        {{-- Передаем нужные данные кейса как параметры --}}
        @livewire('artefacts-case', [
            'id' => $case->id,
            'name' => $case->name,
            'type' => $case->type, // Передаем реальный тип кейса
            'case_cost' => $case->case_cost,
            'case_profit' => $case->case_profit,
            'artefacts' => $case->artefacts, // Если это свойство доступно
            // Можете передать дополнительные параметры, чтобы artefacts-case знал,
            // в каком контексте он отображается (например, чтобы скрыть дату для "простых")
            'is_sample_case' => $this->listType === 'sample',
        ], key('artefacts-case-' . $case->id . '-' . $this->listType)) {{-- Важно: Уникальный ключ для каждого экземпляра в цикле, включаем тип списка --}}
    @empty
        {{-- Сообщение, если список пуст --}}

            @if ($listType === 'sample')
                Чумаданов пока нет.
            @else

            @endif

    @endforelse
</div>
