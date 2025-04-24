@props(['id', 'name', 'type', 'case_cost', 'case_profit', 'artefacts'])

<div class="bg-gray-950 text-white p-2 rounded mb-2 transition text-xs {{ $type === 'in_calendar' ? 'case-tooltip' : '' }}"
     x-data="{ isDragOver: false }"
     :class="{ 'bg-yellow-100 bg-opacity-20': isDragOver }"
     x-on:dragover.prevent="isDragOver = true"
     x-on:dragleave="isDragOver = false"
     {{-- Убрал передачу caseId в drop, так как drop находится в компоненте Case --}}
     x-on:drop="isDragOver = false; $wire.drop(artefactId)"
     data-case
     style="position: relative;" {{-- Делаем контейнер относительно позиционированным --}}
>

    {{-- Кнопка удаления (абсолютное позиционирование в верхнем правом углу) --}}
    <button type="button"
            @click.stop="if (confirm('Вы уверены, что хотите удалить этот кейс?')) { $wire.deleteCase() }" {{-- ИСПРАВЛЕНО: Вызываем $wire.deleteCase() без аргументов --}}
            class="absolute top-1 right-1 p-1 text-red-400 hover:text-red-600 focus:outline-none z-10"
            aria-label="Удалить кейс"
    >
        {{-- Иконка крестика --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- ... остальное содержимое шаблона кейса ... --}}
    <div class="flex items-center justify-between text-[10px]">
        @if($type === 'sample')<h2>{{ $name }}</h2>  @endif
        <span class="flex items-center justify-start ">
            - <img src="{{  asset('/images/m_game3.gif') }}" alt=""> {{ $case_cost }}
        </span>
        <br>
        <span class="flex items-center justify-start">
            + <img src="{{  asset('/images/m_game3.gif') }}" alt=""> {{ $case_profit }}
        </span>
    </div>
    <div class="flex flex-wrap gap-1">
        @forelse($artefacts as $artefact)
            {{-- Убедитесь, что x-on:dblclick на артефакте диспатчит событие с artefactId и caseId --}}
            <x-artefact.artefact :artefact="$artefact" :caseId="$id" :iconSize="$type === 'sample' ? '25' : '16'"/>
        @empty
            <li>Тут пока ничего нету</li>
        @endforelse
    </div>
</div>
