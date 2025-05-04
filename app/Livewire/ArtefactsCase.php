<?php

namespace App\Livewire;

use App\Models\ArtefactsCase as ArtefactsCaseModel; // Импортируем модель с псевдонимом

use App\Models\Artefact;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class ArtefactsCase extends Component
{
    public int $id;
    public string $name;
    public string $type;
    public ?float $case_cost = null;
    public ?float $case_profit = null;
    public ?string $calendar_date = null;
    public ?string $calendar_time = null;
    public ?int $sample_order = 0;
    public ?string $case_description = null;

    public Collection $artefacts;

    public function refreshSelf(): void
    {
        $case = ArtefactsCaseModel::with('artefacts')->find($this->id);
        $this->fill($case->getAttributes());
        $this->artefacts = $case->artefacts;
    }


    public function drop($artefactId, int $artefactCount = 1): void
    {
        $artefact = Artefact::find($artefactId);

        $case = ArtefactsCaseModel::find($this->id);

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
        $case = ArtefactsCaseModel::find($this->id); // Используем псевдоним
        $artefact = Artefact::find($artefactId);

        if ($case && $artefact) {
            // Проверяем количество артефактов в кейсе перед удалением
            $existingPivot = $case->artefacts()->where('artefact_id', $artefactId)->first();

            if ($existingPivot) {

                $case->artefacts()->detach($artefactId);

                // Обновляем стоимость кейса (fetch fresh data)
                $case->refresh(); // Обновляем модель кейса
                $this->case_cost = $case->case_cost;

                // Обновляем коллекцию артефактов компонента
                // Загружаем отношения artefacts заново после изменений
                $this->artefacts = $case->artefacts()->get();

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


    public function deleteCase(): void
    {
        // Находим модель кейса по ID этого компонента
        $case = ArtefactsCaseModel::find($this->id);

        if ($case) {
            // Опционально: Добавьте проверку авторизации/политики, чтобы только владелец мог удалить
             if ($case->user_id !== Auth::id()) {
                 \Log::warning('ArtefactsCase::deleteCase unauthorized attempt', ['caseId' => $this->id, 'userId' => Auth::id()]);
                 session()->flash('error', 'Вы не можете удалить этот кейс.'); // Пример сообщения об ошибке
                 return; // Прекращаем выполнение, если нет прав
             }

            // Удаляем модель кейса (это также удалит связи в промежуточной таблице)
            $case->delete();

            $this->dispatch('case-deleted', caseId: $this->id);

        } else {
            \Log::warning('ArtefactsCase::deleteCase failed: Case not found for deletion', ['caseId' => $this->id]);
            // Опционально: диспатчить событие об ошибке
            // $this->dispatch('notify', message: 'Кейс не найден для удаления.', type: 'error');
        }
    }

    // метод для обновления количества артефактов через инлайн-редактор
    public function updateArtefactCount(int $artefactId, int $newCount): void
    {

        $case = ArtefactsCaseModel::find($this->id); // Используем псевдоним
        $artefact = Artefact::find($artefactId);

        // Проверяем существование кейса и артефакта
        if (!$case || !$artefact) {
            \Log::warning('updateArtefactCount failed: Case or Artefact not found', [
                'caseId' => $this->id,
                'artefactId' => $artefactId
            ]);
            // Опционально: диспатчить событие об ошибке на фронтенд
            $this->dispatch('notify', message: 'Кейс или артефакт не найден.', type: 'error');
            return;
        }

        // Проверяем, что артефакт действительно связан с этим кейсом
        $existingPivot = $case->artefacts()->where('artefact_id', $artefactId)->first();

        if (!$existingPivot) {
            \Log::warning('updateArtefactCount failed: Artefact not in case', [
                'caseId' => $this->id,
                'artefactId' => $artefactId
            ]);
            $this->dispatch('notify', message: 'Артефакт не найден в этом кейсе.', type: 'warning');
            return;
        }


        // Валидируем новое количество
        $validatedCount = max(0, $newCount); // Убеждаемся, что количество не отрицательное

        if ($validatedCount <= 0) {
            // Если новое количество 0 или меньше, отсоединяем артефакт полностью
            $case->artefacts()->detach($artefactId);
        } else {
            // Иначе, обновляем количество в промежуточной таблице
            $case->artefacts()->updateExistingPivot($artefactId, [
                'artefact_in_case_count' => $validatedCount,
            ]);
        }

        // Обновляем свойства компонента, чтобы UI синхронизировался
        // Fetch fresh data для case_cost и коллекции артефактов
        $case->refresh(); // Обновляем модель кейса, чтобы получить свежий case_cost
        $this->case_cost = $case->case_cost; // Обновляем публичное свойство

        // Перезагружаем коллекцию артефактов для компонента
        $this->artefacts = $case->artefacts()->get(); // Загружаем отношения заново

    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        \Log::info('ArtefactsCase Rendered:', [
            'id' => $this->id,
            'name' => $this->name,
            'profit' => $this->case_profit,
            // Опционально: проверить, загружены ли артефакты
            'artefacts_loaded' => isset($this->artefacts) && $this->artefacts instanceof \Illuminate\Support\Collection,
            'artefact_count' => isset($this->artefacts) ? $this->artefacts->count() : 0,
        ]);
        return view('components.artefact.case');
    }
}
