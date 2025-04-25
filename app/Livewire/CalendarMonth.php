<?php

namespace App\Livewire;

use App\Models\Artefact;
use App\Models\ArtefactsCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Services\CalendarService;
use Carbon\Carbon;

class CalendarMonth extends Component
{
    public int $year;
    public int $month;
    public array $grouped = [];
    public array $monthlyEvents = [];

    public Collection $artefacts;
    public Collection $artefactsCases;

    public function mount($year, $month)
    {
        $this->year = $year;
        $this->month = $month;

        $calendarService = new CalendarService();
        $this->grouped = $calendarService->getGroupedEvents(Auth::id());

        // Собираем события для отображения в сайдбаре
        $this->monthlyEvents = $this->getEventsForMonth();

        $this->artefacts = Artefact::where('user_id', auth()->id())
            ->orWhereNull('user_id')
            ->get();

        $this->artefactsCases = ArtefactsCase::where('user_id', auth()->id())
            ->orWhereNull('user_id')
            ->get();
    }

    public function getEventsForMonth(): array
    {
        $start = Carbon::create($this->year, $this->month)->startOfMonth();
        $end = Carbon::create($this->year, $this->month)->endOfMonth();
        $events = [];

        foreach ($this->grouped as $date => $dayEvents) {
            $dateObj = Carbon::parse($date);
            if ($dateObj->between($start, $end)) {
                foreach ($dayEvents as $event) {
                    $events[] = $event;
                }
            }
        }

        return $events;
    }

    public function changeMonth($direction)
    {
        if ($direction === 'previous') {
            $date = Carbon::create($this->year, $this->month)->subMonth();
        } else {
            $date = Carbon::create($this->year, $this->month)->addMonth();
        }

        $this->year = $date->year;
        $this->month = $date->month;

        $calendarService = new CalendarService();
        $this->grouped = $calendarService->getGroupedEvents(Auth::id());
        $this->monthlyEvents = $this->getEventsForMonth();

        $this->dispatch('dom-updated');
    }

    // --- ДОБАВЛЯЕМ МЕТОД ДЛЯ КОПИРОВАНИЯ КЕЙСА В КАЛЕНДАРЬ ---
    public function copyCaseToCalendar($sampleCaseId, $dropDate)
    {
        Log::info('CalendarComponent: copyCaseToCalendar called', ['sampleCaseId' => $sampleCaseId, 'dropDate' => $dropDate]);

        // 1. Находим исходный Sample Case
        $originalCase = ArtefactsCase::where('id', $sampleCaseId)
            ->where('type', 'sample') // Убедимся, что копируем именно Sample
            ->first();

        if (!$originalCase) {
            Log::warning('CalendarComponent: copyCaseToCalendar failed - Original sample case not found', ['sampleCaseId' => $sampleCaseId]);
            // Опционально: диспатчить событие об ошибке на фронтенд
            // $this->dispatch('notify', message: 'Исходный шаблон кейса не найден.', type: 'error');
            return;
        }

        // Убедимся, что дата валидна
        if (!strtotime($dropDate)) {
            Log::warning('CalendarComponent: copyCaseToCalendar failed - Invalid drop date', ['dropDate' => $dropDate]);
            // $this->dispatch('notify', message: 'Неверная дата для копирования кейса.', type: 'error');
            return;
        }


        // 2. Создаем реплику (копию) модели
        // replicate() копирует атрибуты, но не ID и отношения
        $newCase = $originalCase->replicate();

        // 3. Изменяем необходимые атрибуты для нового кейса
        $newCase->type = 'in_calendar'; // Изменяем тип
        $newCase->calendar_date = $dropDate; // Устанавливаем дату
        $newCase->calendar_time = null; // Сбрасываем время или устанавливаем по умолчанию, если нужно
        $newCase->sample_order = 0; // Сбрасываем порядок, т.к. это больше не sample
        $newCase->user_id = Auth::id(); // Устанавливаем текущего пользователя как владельца копии

        // Сбрасываем ID, чтобы Eloquent создал новую запись
        // replicate() обычно делает это автоматически, но явное указание не повредит
        // $newCase->id = null; // replicate() делает это

        // 4. Сохраняем новую запись в БД
        $newCase->save();

        Log::info('CalendarComponent: New case created', ['newCaseId' => $newCase->id, 'type' => $newCase->type, 'date' => $newCase->calendar_date]);

        // 5. Копируем связи с артефактами
        // Получаем ID артефактов из оригинального кейса
        $artefactIds = $originalCase->artefacts()->pluck('artefacts.id'); // Убедитесь, что 'artefacts.id' правильное имя столбца

        // Присоединяем эти артефакты к новому кейсу
        if ($artefactIds->isNotEmpty()) {
            $newCase->artefacts()->attach($artefactIds);
            Log::info('CalendarComponent: Artefacts attached to new case', ['newCaseId' => $newCase->id, 'artefactIds' => $artefactIds->toArray()]);
        } else {
            Log::info('CalendarComponent: No artefacts to attach for new case', ['newCaseId' => $newCase->id]);
        }


        // 6. Уведомляем компонент списка кейсов для этого дня о необходимости обновиться
        // Диспатчим событие, которое поймает CaseListComponent для конкретной даты.
        // Событие глобальное ('case-copied-to-day').
        // Передаем дату, чтобы нужный компонент списка среагировал.
        $this->dispatch('case-copied-to-day', date: $dropDate);
        $this->dispatch('dom-updated');

        // Опционально: Флеш-сообщение об успехе
        // session()->flash('message', 'Кейс успешно скопирован в календарь!');

        Log::info('CalendarComponent: copyCaseToCalendar finished successfully', ['newCaseId' => $newCase->id]);

        // Livewire автоматически перерисует календарь, если какие-то свойства компонента календаря изменились.
        // Главное - уведомить ДОЧЕРНИЙ CaseListComponent для конкретного дня обновиться.

    }

    public function render()
    {
        $monthName = Carbon::create($this->year, $this->month)->translatedFormat('F');
        return view('livewire.calendar-month', [
            'monthName' => $monthName,
        ]);
    }
}
