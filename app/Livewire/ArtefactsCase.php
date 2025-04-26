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

    public function drop($artefactId, int $artefactCount = 1): void
    {
        $artefact = Artefact::find($artefactId);

        $case = \App\Models\ArtefactsCase::find($this->id);

        if ($case && $artefact) {
            // Ищем артефакт в целевом кейсе
            $existingArtefact = $case->artefacts()->where('artefact_id', $artefactId)->first();

            if ($existingArtefact) {
                // Безопасно получаем старое количество
                $currentCount = $existingArtefact->pivot->artefact_in_case_count ?? 1;

                // Обновляем количество: старое + добавляемое
                $case->artefacts()->updateExistingPivot($artefactId, [
                    'artefact_in_case_count' => $currentCount + $artefactCount,
                ]);
            } else {
                // Если артефакта нет в кейсе — добавляем с нужным количеством
                $case->artefacts()->attach($artefactId, [
                    'artefact_in_case_count' => $artefactCount,
                ]);
            }

            // Обновляем стоимость кейса
            $this->case_cost = $case->fresh()->case_cost;

            // Обновляем коллекцию артефактов
            $this->artefacts = $case->fresh()->artefacts;
        }
    }

    public function handleArtefactDoubleClick($artefactId): void
    {
        // Находим модель кейса и артефакта
        $case = \App\Models\ArtefactsCase::find($this->id); // Используем псевдоним
        $artefact = Artefact::find($artefactId);

        if ($case && $artefact) {
            // Проверяем количество артефактов в кейсе перед удалением
            $existingPivot = $case->artefacts()->where('artefact_id', $artefactId)->first();

            if ($existingPivot) {

                $case->artefacts()->detach($artefactId);
                \Log::info("Detached artefact {$artefactId} from case {$this->id}.");

                // Обновляем стоимость кейса (fetch fresh data)
                $case->refresh(); // Обновляем модель кейса
                $this->case_cost = $case->case_cost;
                \Log::info("Case cost updated for case {$this->id}. New cost: " . $this->case_cost);


                // Обновляем коллекцию артефактов компонента
                // Загружаем отношения artefacts заново после изменений
                $this->artefacts = $case->artefacts()->get();
                \Log::info("Artefacts collection refreshed for case {$this->id}.");


                // !!! ОПЦИОНАЛЬНО: Диспатчим событие вверх к родительскому компоненту !!!
                // Это уведомит родителя об изменении в этом дочернем кейсе.
                // Родитель должен слушать событие 'artefact-removed-from-case'.
//                $this->dispatch('artefact-removed-from-case',
//                    caseId: $this->id, // ID кейса, из которого удален артефакт
//                    artefactId: $artefactId // ID удаленного артефакта
//                )->up(); // Отправляем событие вверх по дереву компонентов

                \Log::info('Dispatched artefact-removed-from-case event up', [
                    'caseId' => $this->id,
                    'artefactId' => $artefactId
                ]);


            } else {
                \Log::warning("Attempted to remove artefact {$artefactId} from case {$this->id}, but it was not found in the pivot table.");
                // Опционально: диспатчить событие об ошибке или флеш-сообщение
                // $this->dispatch('notify', message: 'Артефакт не найден в кейсе.', type: 'warning');
            }


        } else {
            \Log::warning('handleArtefactDoubleClick failed: Case or Artefact not found', [
                'caseId' => $this->id,
                'artefactId' => $artefactId
            ]);
            // Опционально: диспатчить событие об ошибке
            // $this->dispatch('notify', message: 'Кейс или артефакт не найден.', type: 'error');
        }
    }


//    public function removeArtefact($artefactId, $caseId): void
//    {
//        if (!$caseId || $this->id != $caseId) return; // Только если удаляется из этого кейса
//
//        $case = \App\Models\ArtefactsCase::find($this->id);
//        $artefact = Artefact::find($artefactId);
//
//        if ($case && $artefact) {
//            $case->artefacts()->detach($artefactId);
//
//            $this->case_cost = $case->fresh()->case_cost;
//            $this->artefacts = $case->artefacts()->get();
//        }
//    }

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
