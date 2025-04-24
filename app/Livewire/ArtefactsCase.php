<?php

namespace App\Livewire;

use App\Models\Artefact;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ArtefactsCase extends Component
{
    public int $id;
    public string $name;
    public string $type;
    public ?float $case_cost = null;
    public ?float $case_profit = null;
    public ?string $calendar_date = null;
    public ?string $calendar_time = null;
    public int $sample_order = 0;

    public Collection $artefacts;

    protected $listeners = [
        'artefact-double-click' => 'removeArtefact',
    ];

    public function drop($artefactId): void
    {
        $artefact = Artefact::find($artefactId);

        $case = \App\Models\ArtefactsCase::where('id', $this->id)->first();

        if ($case && $artefact) {
            $case->artefacts()->attach($artefact);

            $this->case_cost = $case->fresh()->case_cost;

            $this->artefacts->push($artefact);
        }
    }

    public function removeArtefact($artefactId, $caseId): void
    {
        if (!$caseId || $this->id != $caseId) return; // Только если удаляется из этого кейса

        $case = \App\Models\ArtefactsCase::find($this->id);
        $artefact = Artefact::find($artefactId);

        if ($case && $artefact) {
            $case->artefacts()->detach($artefactId);
            $this->artefacts = $this->artefacts->filter(fn($a) => $a->id != $artefactId);
            $this->case_cost = $case->fresh()->case_cost;
        }
    }

    public function deleteCase(): void
    {
        \Log::info('ArtefactsCase::deleteCase called', ['caseId' => $this->id]);

        // Находим модель кейса по ID этого компонента
        $case = \App\Models\ArtefactsCase::find($this->id);

        if ($case) {
            // Опционально: Добавьте проверку авторизации/политики, чтобы только владелец мог удалить
             if ($case->user_id !== Auth::id()) {
                 \Log::warning('ArtefactsCase::deleteCase unauthorized attempt', ['caseId' => $this->id, 'userId' => Auth::id()]);
                 session()->flash('error', 'Вы не можете удалить этот кейс.'); // Пример сообщения об ошибке
                 return; // Прекращаем выполнение, если нет прав
             }

            // Удаляем модель кейса (это также удалит связи в промежуточной таблице)
            $case->delete();

            \Log::info('ArtefactsCase::deleteCase case deleted', ['caseId' => $this->id]);

            // Диспатчим событие ВВЕРХ по дереву компонентов, чтобы родительский компонент списка
            // узнал об удалении и убрал этот кейс из своей коллекции.
            // 'case-deleted' - название события
            // caseId: $this->id - передаем ID удаленного кейса в данных события
            // ->up() - указывает Livewire отправить событие вверх по дереву
            $this->dispatch('case-deleted', caseId: $this->id);

        } else {
            \Log::warning('ArtefactsCase::deleteCase failed: Case not found for deletion', ['caseId' => $this->id]);
            // Опционально: диспатчить событие об ошибке
            // $this->dispatch('notify', message: 'Кейс не найден для удаления.', type: 'error');
        }
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.artefact.case');
    }
}
