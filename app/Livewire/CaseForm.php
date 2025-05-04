<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ArtefactsCase;
use Illuminate\Support\Facades\Auth;

class CaseForm extends Component
{
    public ?int $caseId = null;
    public string $name = '';
    public string $type = 'in_calendar';
    public ?string $calendar_date = '';
    public ?string $calendar_time = '';
    public ?int $sample_order = 0;
    public ?float $case_cost = null;
    public ?float $case_profit = null;
    public ?string $case_description = null;


    protected function rules()
    {
        // Базовые правила, которые всегда применяются
        $baseRules = [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:in_calendar,sample',
            'sample_order' => 'nullable|sometimes|integer',
            'case_cost' => 'nullable|sometimes|numeric',
            'case_profit' => 'nullable|sometimes|numeric',
            'case_description' => 'nullable|string|max:10000',
        ];

        // Условные правила, которые применяются только для in_calendar
        $conditionalRules = [];
        if ($this->type === 'in_calendar') {
            $conditionalRules = [
                'calendar_date' => 'required|date|date_format:Y-m-d',
                // calendar_time может быть nullable даже для in_calendar, если оно не обязательное для заполнения
                'calendar_time' => 'nullable|string',
            ];
        } else {
            // Если тип НЕ in_calendar, убедитесь, что эти поля не требуют заполнения
            // и, если они не показываются, их значения игнорируются или обнуляются
            $conditionalRules = [
                'calendar_date' => 'nullable', // Явно указываем, что не требуется
                'calendar_time' => 'nullable|string',
            ];
        }

        // Объединяем базовые и условные правила
        return array_merge($baseRules, $conditionalRules);
    }


    public function mount()
    {
         $this->reset();
    }

    public function loadFormData(array $data = [])
    {
        // Всегда начинаем со сброса формы, чтобы избежать "наложения" данных
        // от предыдущего открытия модалки (для создания или редактирования).
        $this->resetForm();

        // Проверяем, передан ли ID кейса для редактирования
        if (isset($data['id']) && $case = ArtefactsCase::find($data['id'])) {
            // Если ID передан и кейс найден, загружаем его данные
            $this->fill($case->toArray());
            $this->caseId = $case->id;
        } else {
            // Если ID не передан или кейс не найден, готовим форму для создания нового
            // Устанавливаем тип и дату, если они были переданы (например, при клике на день календаря)
            $this->type = $data['type'] ?? 'in_calendar'; // Устанавливаем тип, по умолчанию 'in_calendar'
            $this->calendar_date = $data['date'] ?? ''; // Устанавливаем дату, если передана
            $this->caseId = null; // Убеждаемся, что caseId null для нового кейса
        }
    }
    public function store()
    {
        // Валидация по текущим правилам
        $this->validate();

        // Подготавливаем данные для сохранения/обновления
        $data = $this->only([
            'name',
            'type',
            'calendar_date',
            'calendar_time',
            'sample_order',
            'case_cost',
            'case_profit',
            'case_description',
        ]);

        // Приводим пустые строки к null для полей, которые могут быть nullable
        $data['calendar_date'] = $data['calendar_date'] ?: null;
        $data['calendar_time'] = $data['calendar_time'] ?: null;
        $data['sample_order'] = $data['sample_order'] ?: null;
        $data['case_cost'] = $data['case_cost'] ?: null;
        $data['case_profit'] = $data['case_profit'] ?: null;
        $data['case_description'] = $data['case_description'] ?: null;
        $data['user_id'] = Auth::id(); // Добавляем ID пользователя

        if ($this->caseId) {
            // Если caseId существует, ищем кейс и обновляем его
            $case = ArtefactsCase::find($this->caseId);
            if ($case) {
                $case->update($data);
                $this->dispatch('case-updated', ['id' => $this->caseId]);
            } else {
                \Log::warning('CaseForm: Attempted to update non-existing case', ['id' => $this->caseId]);
                // Опционально: можно диспатчить событие об ошибке или показать сообщение
            }

        } else {
            // Если caseId отсутствует, создаем новый кейс
            $newCase = ArtefactsCase::create($data);
            \Log::info('CaseForm: New case created successfully', ['id' => $newCase->id]);
            $this->dispatch('case-added');
        }
    }

    public function resetForm()
    {
        \Log::info('Livewire CaseForm: resetForm called'); // Лог для отладки

        // Сбрасываем ВСЕ поля формы к их начальным значениям, как при первом монтировании
        $this->reset([
            'name',
            'type', // Сбрасываем тип
            'calendar_date',
            'calendar_time',
            'sample_order',
            'case_cost',
            'case_profit',
            'case_description',
            'caseId'
        ]);

        // Возможно, нужно явно установить тип по умолчанию после сброса
        $this->type = 'in_calendar'; // Устанавливаем дефолтное значение для типа
        $this->sample_order = 0; // Устанавливаем дефолтное значение для sample_order
    }

    // Метод render остается без изменений
    public function render()
    {
        return view('livewire.case-form');
    }


}
