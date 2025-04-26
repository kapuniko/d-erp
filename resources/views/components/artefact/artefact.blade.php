@props(['artefact', 'caseId' => null, 'iconSize'])

<div draggable="true"  x-on:dragstart="
                            artefactId = {{ $artefact->id }};
                            artefactCount = {{ $artefact->pivot?->artefact_in_case_count ?? 1 }}
                            artefactFromCaseId = {{ $caseId ?? 'null' }}
                            "
                         @if($caseId)
                             x-on:dblclick="
                               $wire.handleArtefactDoubleClick({{ $artefact->id }})
                            "
                        @endif
                         @dragstart="artefactIsDragging = true;"
                         @dragend="artefactIsDragging = false"
                         class="artefact-icon__size-{{ $iconSize }}"
                    >
    <div class="artefact-tooltip">
        <img src="https://w1.dwar.ru/{{ $artefact->image }}" alt="{{ $artefact->name }}" class="artefact-icon__size-{{ $iconSize }}" />

        {{-- --- БЛОК ДЛЯ РЕДАКТИРОВАНИЯ КОЛИЧЕСТВА --- --}}
        {{-- Только если артефакт находится в кейсе ($caseId определен) --}}
        @if($caseId)
            {{-- Обертка для спана и инпута с Alpine.js состоянием --}}
            <div class="artefact_in_case_count_container"
                 x-data="{ editingCount: false, newCount: {{ $artefact->pivot?->artefact_in_case_count ?? 1 }} }"
                 x-on:click.outside="if(editingCount) { $wire.updateArtefactCount({{ $artefact->id }}, newCount); editingCount = false }" {{-- Сохраняем при клике вне блока --}}
                 x-init="$watch('editingCount', value => { if (value) $nextTick(() => $refs.countInput.focus()) })" {{-- Фокус на инпут при появлении --}}
            >
                {{-- Спан для отображения количества (показывается, когда не редактируем) --}}
                <span class="artefact_in_case_count"
                      x-show="!editingCount"
                      x-on:contextmenu.prevent.stop="editingCount = true" {{-- Показываем инпут по правому клику, отключаем стандартное меню --}}
                >
                    {{ $artefact->pivot?->artefact_in_case_count ?? 1 }}
                </span>

                {{-- Поле ввода для редактирования (показывается, когда редактируем) --}}
                <input type="number"
                       x-show="editingCount"
                       x-ref="countInput" {{-- Ссылка для Alpine на этот элемент --}}
                       x-model.number="newCount" {{-- Связываем значение инпута с переменной newCount --}}
                       x-on:keydown.enter="$wire.updateArtefactCount({{ $artefact->id }}, newCount); editingCount = false" {{-- Сохраняем при Enter --}}
                       x-on:keydown.escape="editingCount = false; newCount = {{ $artefact->pivot?->artefact_in_case_count ?? 1 }}" {{-- Отмена при Escape, сброс значения --}}
                       class="artefact_count_input"
                       style="display: none"
                >
            </div>
        @endif
        {{-- --- КОНЕЦ БЛОКА ДЛЯ РЕДАКТИРОВАНИЯ КОЛИЧЕСТВА --- --}}

        <div class="artefact-content">
            <strong>{{ $artefact->name }}</strong><br>
            Время: {{ $artefact->duration_sec }} сек.
        </div>
    </div>
</div>
