@props(['id', 'name', 'type', 'case_cost', 'case_profit', 'artefacts', 'calendar_time', 'case_description'])

<div class="bg-gray-900 text-white p-1 rounded mb-1 transition text-xs {{ $type === 'in_calendar' ? 'calendar-tooltip' : '' }}"
     id="case-{{$id}}"
     x-data="{
        isDragOver: false,
        caseId: {{ $id }},
     }"
     x-on:dragover.prevent="isDragOver = true"
     x-on:dragleave="isDragOver = false"
     x-on:drop.stop="isDragOver = false;
                        const caseId = event.dataTransfer.getData('case-id');
                        if (!caseId) {
                            $wire.drop(artefactId, artefactCount);
                        }"
     data-case
     style="position: relative;" {{-- Делаем контейнер относительно позиционированным --}}
     draggable="{{ $type === 'sample' ? 'true' : 'false' }}" {{-- Только sample кейсы перетаскиваются --}}
     @dragstart="event.dataTransfer.setData('case-id', caseId); event.dataTransfer.effectAllowed = 'copy';" {{-- Сохраняем ID и указываем тип операции "копирование" --}}
     @dragend="isDragOver = false" {{-- Опционально: для стилей после окончания перетаскивания --}}
     :class="{ 'bg-yellow-100 bg-opacity-20': isDragOver, 'highlightGreen': artefactIsDragging }"
>
    @if($type === 'in_calendar')
        <div class="calendar-tooltip__content tooltip__bottom">
            <h2>{{ $name }}</h2>
            <p>Время: {{ $calendar_time }}</p>

            @if($case_description)
                <p>{{ $case_description }}</p>
            @endif

            <!-- Кнопка редактирования -->
            @if (Auth::user())
                <br>
                <div class="flex items-center justify-between">
                <button
                    class="edit-case-btn text-gray-200 hover:text-white"
                    @click="$dispatch('open-case-form-modal', { id: caseId }); showModal = true;"
                    title="Редактировать"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="size-3">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m16.862 4.487 1.687-1.688a1.875 1.875
                              0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897
                              1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863
                              4.487Zm0 0L19.5 7.125" />
                    </svg>
                </button>

                <button type="button"
                        @click.stop="if (confirm('Вы уверены, что хотите удалить этот чумадан?')) { $wire.deleteCase() }" {{-- ИСПРАВЛЕНО: Вызываем $wire.deleteCase() без аргументов --}}
                        class="text-red-400 hover:text-red-600 focus:outline-none z-10"
                        aria-label="Удалить кейс"
                >
                    {{-- Иконка крестика --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="4" stroke="currentColor" class="w-3 h-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                </div>
            @endif
        </div>
    @endif

    <div class="flex items-center justify-between text-[10px]">
        @if($type === 'sample')<h2>{{ $name }}</h2>  @endif
        <span class="flex items-center justify-start ">
            - <img src="{{  asset('/images/m_game3.gif') }}" alt=""> {{ $case_cost }}
        </span>
        <div class="w-4"> </div>
        <span class="flex items-center justify-start">
            + <img src="{{  asset('/images/m_game3.gif') }}" alt=""> {{ $case_profit }}
        </span>
    </div>
    <div class="flex flex-wrap gap-1">
        @forelse($artefacts as $artefact)
            {{-- Убедитесь, что x-on:dblclick на артефакте диспатчит событие с artefactId и caseId --}}
            <x-artefact.artefact
                :artefact="$artefact"
                :caseId="$id"
                :iconSize="$type === 'sample' ? '25' : '16'"
                wire:key="artefact-{{ $artefact->id }}"
            />
        @empty
            <li>Тут пока ничего нету</li>
        @endforelse
    </div>
</div>
