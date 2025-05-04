<div class="case-list-container">
    @forelse($cases as $case)
        <livewire:artefacts-case
            :id="$case->id"
            :name="$case->name"
            :type="$case->type"
            :case_cost="$case->case_cost"
            :case_profit="$case->case_profit"
            :artefacts="$case->artefacts"
            :is_sample_case="$this->listType === 'sample'"
            :calendar_time="$case->calendar_time"
            :case_description="$case->case_description"
            wire:key="case-{{ $case->id }}"
        />
    @empty
        {{-- Сообщение, если список пуст --}}

            @if ($listType === 'sample')
                Чумаданов пока нет.
            @else

            @endif

    @endforelse
</div>
