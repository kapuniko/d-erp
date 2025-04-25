<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Models\ArtefactsCase;
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
        'case-copied-to-day' => 'refreshIfMatchesDate', // Событие 'case-copied-to-day' обрабатывается методом refreshIfMatchesDate
        'case-deleted' => 'removeCaseFromList', // Слушаем глобальное событие 'case-deleted'
        // ... другие слушатели ...
    ];

    // Этот метод будет вызван при срабатывании события
    public function removeCaseFromList($caseId)
    {
        \Log::info('CaseListComponent: received case-deleted event', ['deletedCaseId' => $caseId]);

        // Обновляем коллекцию кейсов
        $this->cases = $this->cases->filter(fn($case) => $case->id != $caseId);

        // Livewire перерисует список
    }

    // Метод mount принимает тип списка и опционально дату
    public function mount(string $listType, ?string $date = null)
    {
        $this->listType = $listType;
        $this->date = $date; // Сохраняем дату, если она передана

        // Выполняем базовую валидацию типа
        // $this->validateOnly('listType'); // Можно использовать validateOnly, если нужно

        // Загружаем соответствующие кейсы при монтировании
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

    // --- Метод, вызываемый при получении события 'case-copied-to-day' ---
    public function refreshIfMatchesDate($date)
    {
        Log::info('CaseListComponent: received case-copied-to-day event', ['eventDate' => $date, 'myDate' => $this->date, 'myType' => $this->listType]);

        // Проверяем, что этот компонент является списком 'in_calendar' и его дата
        // соответствует дате, куда был скопирован кейс.
        if ($this->listType === 'in_calendar' && $this->date === $date) {
            Log::info('CaseListComponent: Date match, refreshing cases', ['date' => $date]);
            $this->loadCases(); // Перезагружаем кейсы для этой даты
            // Livewire автоматически увидит изменение в $this->cases и перерисует компонент
        } else {
            Log::info('CaseListComponent: Date or type mismatch, not refreshing.');
        }
    }

    // ... Метод removeCaseFromList($caseId) для обработки удаления (если этот компонент управляет удалением) ...
    // public function removeCaseFromList($caseId) { ... }


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
