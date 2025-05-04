<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Models\ArtefactsCase;
use App\Models\Artefact;
use Livewire\Attributes\On;
use Illuminate\Support\Collection; // Импортируем Collection

class CaseListComponent extends Component
{
    // Публичные свойства для конфигурации компонента
    public string $listType; // 'in_calendar' или 'sample'
    public ?string $date = null; // Дата, только для типа 'in_calendar'

    // Публичное свойство для хранения коллекции кейсов для отображения
    public Collection $cases;

    // Опционально: правила валидации для переданных параметров (basic check)
    protected $rules = [
        'listType' => 'required|in:in_calendar,sample',
    ];

    protected $listeners = [
        'add-sample-case-to-list-event' => 'addSampleCaseLogic', // Слушаем это новое событие
        'case-deleted' => 'removeCaseFromList', // Слушаем глобальное событие 'case-deleted'
    ];

    // Этот метод будет вызван при срабатывании события
    public function removeCaseFromList($caseId)
    {
        $this->cases = $this->cases->filter(fn($case) => $case->id != $caseId);
    }

    // Метод mount принимает тип списка и опционально дату
    public function mount(string $listType, ?string $date = null)
    {
        $this->listType = $listType;
        $this->date = $date;
        $this->loadCases();
    }

    // Слушатель события 'case-added'
    // Это событие будет вызвано после сохранения нового кейса в CaseForm
    #[On('case-added')] // Livewire v3 синтаксис
        // protected $listeners = ['case-added' => 'refreshList']; // Livewire v2 синтаксис

    public function refreshList()
    {
        // При получении события, просто перезагружаем список кейсов
        // loadCases() сам определит, какие кейсы нужны, основываясь на $this->listType и $this->date
        $this->loadCases();
    }

    // Приватный метод для загрузки кейсов из базы данных
    private function loadCases()
    {
        // Начинаем запрос, фильтруя по текущему пользователю
        $query = ArtefactsCase::where('user_id', auth()->id());

        // Применяем фильтрацию в зависимости от типа списка
        if ($this->listType === 'in_calendar') {
            // Для кейсов в календаре, фильтруем по типу и по дате
            if (empty($this->date)) {
                // Если тип 'in_calendar', но дата не передана (чего быть не должно при правильном использовании)
                // Можно вернуть пустую коллекцию или бросить ошибку, или залогировать.
                $this->cases = collect(); // Вернуть пустую коллекцию
                \Log::warning("CaseListComponent (in_calendar) mounted without a date."); // Лог
                return; // Прекратить выполнение метода
            }
            $query->where('type', 'in_calendar')
                ->where('calendar_date', $this->date) // Фильтр по дате, переданной в mount
                ->orderBy('calendar_time'); // Добавляем сортировку по времени для календаря

        } elseif ($this->listType === 'sample') {
            // Для "простых" кейсов (шаблонов), фильтруем только по типу
            $query->where('type', 'sample')
                ->orderBy('created_at'); // Пример сортировки для шаблонов
            // Дата для этого типа списка не используется

        } else {
            // Неизвестный тип списка - такого быть не должно, если используется валидация или enum
            $this->cases = collect(); // Вернуть пустую коллекцию
            \Log::error("CaseListComponent mounted with unknown list type: " . $this->listType); // Лог ошибки
            return; // Прекратить выполнение метода
        }

        // Выполняем запрос и сохраняем коллекцию в публичное свойство
        $this->cases = $query->get();
    }


    // <-- Метод для обработки события сброса sample кейса -->
    // Вызывается, когда ЛЮБОЙ экземпляр CaseListComponent ловит событие 'add-sample-case-to-list-event'
    public function addSampleCaseLogic($sampleCaseId, $targetDate)
    {

        // <-- ВАЖНО: Проверяем, что этот экземпляр CaseListComponent - ЦЕЛЕВОЙ -->
        // Только in_calendar список с правильной датой должен обрабатывать событие
        if ($this->listType === 'in_calendar' && $this->date === $targetDate) {

            // 1. Находим исходный Sample Case
            $originalCase = ArtefactsCase::with('artefacts')
                ->where('id', $sampleCaseId)
                ->where('type', 'sample')
                ->first();

            if (!$originalCase) {
                Log::warning('CaseListComponent::addSampleCaseLogic failed - Original sample case not found', ['sampleCaseId' => $sampleCaseId]);
                // Dispatch error event if needed (e.g., $this->dispatch('notify', ...))
                return;
            }

            // 2. Создаем реплику (копию) модели
            $newCase = $originalCase->replicate();

            // 3. Изменяем необходимые атрибуты для нового кейса
            $newCase->type = 'in_calendar';
            $newCase->calendar_date = $this->date; // Используем дату, которую отображает ЭТОТ компонент списка
            $newCase->calendar_time = null; // Или другое дефолтное значение
            $newCase->sample_order = 0;
            $newCase->user_id = Auth::id();

            // 4. Сохраняем новую запись в БД
            $newCase->save();

            // 5. Копируем связи с артефактами, включая количество из pivot
            $pivotData = $originalCase->artefacts->pluck('pivot.artefact_in_case_count', 'id')->map(function ($count) {
                return ['artefact_in_case_count' => $count];
            })->toArray();

            if (!empty($pivotData)) {
                $newCase->artefacts()->attach($pivotData);
            } else {
                Log::info('CaseListComponent::addSampleCaseLogic: No artefacts to attach', ['newCaseId' => $newCase->id]);
            }

            // 6. Добавляем новый кейс в коллекцию *этого компонента* (без перезагрузки всех кейсов)
            // Eager load artefacts для нового кейса, чтобы он отобразился сразу с артефактами в x-artefact.case
            $newCase->load('artefacts');
            $this->cases->push($newCase); // Добавляем новый кейс в конец коллекции

            // Опционально: отсортировать коллекцию, если важен порядок
            $this->cases = $this->cases->sortBy('calendar_time');

        } else {
            // Этот экземпляр компонента списка не является целью, игнорируем событие.
            Log::info('CaseListComponent::addSampleCaseLogic - Date or type mismatch, ignoring event.', ['targetDate' => $targetDate, 'myDate' => $this->date, 'myListType' => $this->listType]);
        }
    }

    public function render()
    {
        // Рендерим представление компонента.
        // Переменная $cases (коллекция) будет доступна в livewire/case-list-component.blade.php
        // Также передаем listType, если он нужен для условного отображения в шаблоне (например, заголовок)
        return view('livewire.case-list-component', [
            'cases' => $this->cases,
            'listType' => $this->listType, // Может пригодиться в шаблоне
        ]);
    }

    // Метод setDate из предыдущего примера для модалки больше не нужен здесь,
    // так как mount получает дату (если нужно) при инициализации,
    // а refreshList() просто перезагружает данные по сохраненным параметрам ($this->date, $this->listType).
}
