@props(['artefact', 'caseId' => null, 'iconSize'])

@php
$artefact_count = $artefact->pivot?->artefact_in_case_count ?? 1;
 @endphp

<div draggable="{{ $caseId == null ? 'true' : 'false' }}"  x-on:dragstart="
                            artefactId = {{ $artefact->id }};
                            artefactCount = {{ $artefact_count }}
                            artefactFromCaseId = {{ $caseId ?? 'null' }}
                            "
                         @if($caseId)
                             x-on:dblclick="
                               $wire.handleArtefactDoubleClick({{ $artefact->id }})
                            "
                        @endif
                         @dragstart="if ($el.draggable) {
                                            artefactIsDragging = true;
                                        }"
                         @dragend="artefactIsDragging = false"
                         class="artefact-icon__size-{{ $iconSize }}"
                    >
    <div class="calendar-tooltip">
        <img src="https://w1.dwar.ru/{{ $artefact->image }}" alt="{{ $artefact->name }}" class="artefact-icon__size-{{ $iconSize }}" />

        {{-- --- БЛОК ДЛЯ РЕДАКТИРОВАНИЯ КОЛИЧЕСТВА --- --}}
        {{-- Только если артефакт находится в кейсе ($caseId определен) --}}
        @if($caseId)
            {{-- Обертка для спана и инпута с Alpine.js состоянием --}}
            <div class="artefact_in_case_count_container"
                 x-data="{ editingCount: false, newCount: {{ $artefact_count }} }"
                 x-on:click.outside="if(editingCount) { $wire.updateArtefactCount({{ $artefact->id }}, newCount); editingCount = false }" {{-- Сохраняем при клике вне блока --}}
                 x-init="$watch('editingCount', value => { if (value) $nextTick(() => $refs.countInput.focus()) })" {{-- Фокус на инпут при появлении --}}
            >
                {{-- Спан для отображения количества (показывается, когда не редактируем) --}}
                <span class="artefact_in_case_count"
                      x-show="!editingCount"
                      x-on:contextmenu.prevent.stop="editingCount = true" {{-- Показываем инпут по правому клику, отключаем стандартное меню --}}
                >
                    {{ $artefact_count }}
                </span>

                {{-- Поле ввода для редактирования (показывается, когда редактируем) --}}
                <input type="number"
                       x-show="editingCount"
                       x-ref="countInput" {{-- Ссылка для Alpine на этот элемент --}}
                       x-model.number="newCount" {{-- Связываем значение инпута с переменной newCount --}}
                       x-on:keydown.enter="$wire.updateArtefactCount({{ $artefact->id }}, newCount); editingCount = false" {{-- Сохраняем при Enter --}}
                       x-on:keydown.escape="editingCount = false; newCount = {{ $artefact_count }}" {{-- Отмена при Escape, сброс значения --}}
                       class="artefact_count_input"
                       style="display: none"
                >
            </div>
        @endif
        {{-- --- КОНЕЦ БЛОКА ДЛЯ РЕДАКТИРОВАНИЯ КОЛИЧЕСТВА --- --}}

        <div class="calendar-tooltip__content tooltip__top">
            <strong>{{ $artefact->name }}</strong><br>
            Время: {{  round($artefact->duration_sec / 60, 2) }} мин.<br>
            <span class="flex items-center">
                Стоимость:
                @if($artefact_count === 1)
                    <img src="{{  asset('/images/m_game3.gif') }}" alt="Золотой"> {{ $artefact->price }}
                @else
                    <img src="{{  asset('/images/m_game3.gif') }}" alt="Золотой"> {{ $artefact->price }} х {{ $artefact_count }} шт. = <img src="{{  asset('/images/m_game3.gif') }}" alt=""> {{ $artefact->price * $artefact_count }}
                @endif
            </span>
        </div>
    </div>
</div>
