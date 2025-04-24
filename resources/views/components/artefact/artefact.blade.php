@props(['artefact', 'caseId' => null, 'iconSize'])
<div draggable="true"  x-on:dragstart="
                            artefactId = {{ $artefact->id }};
                            artefactFromCaseId = {{ $caseId ?? 'null' }}
                            "
                         @if($caseId)
                             x-on:dblclick="
                                Livewire.dispatch('artefact-double-click', {
                                    artefactId: {{ $artefact->id }},
                                    caseId: {{ $caseId }}
                                })
                            "
                        @endif
                    >
    <div class="artefact-tooltip">
        <img src="https://w1.dwar.ru/{{ $artefact->image }}" alt="{{ $artefact->name }}" class="artefact-icon__size-{{ $iconSize }}" />

        <div class="artefact-content">
            <strong>{{ $artefact->name }}</strong><br>
            Время: {{ $artefact->duration_sec }} сек.
        </div>
    </div>
</div>
