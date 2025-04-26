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
        @if($caseId) <span class="artefact_in_case_count">{{ $artefact->pivot?->artefact_in_case_count ?? 1 }}</span>  @endif
        <div class="artefact-content">
            <strong>{{ $artefact->name }}</strong><br>
            Время: {{ $artefact->duration_sec }} сек.
        </div>
    </div>
</div>
