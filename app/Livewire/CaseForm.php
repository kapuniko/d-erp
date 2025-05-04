<?php

namespace App\Livewire;

use Livewire\Attributes\Rule;
use Livewire\Component;
use App\Models\ArtefactsCase;
use Illuminate\Support\Facades\Auth;

class CaseForm extends Component
{
    #[Rule('nullable', 'integer')]
    public ?int $caseId = null;

    #[Rule('required', 'string', 'max:255')]
    public string $name = '';

    #[Rule('required', 'string', 'in:in_calendar,sample')]
    public string $type = 'in_calendar';

    // Условные правила для calendar_date и calendar_time
    #[Rule('required_if:type,in_calendar', 'nullable', 'date', 'date_format:Y-m-d')]
    public ?string $calendar_date = '';

    #[Rule('nullable', 'string')] // calendar_time всегда nullable, валидация его наличия зависит от required_if на date
    public ?string $calendar_time = '';

    // Сохраняем типизацию ?int
    #[Rule('nullable', 'sometimes', 'integer')] // sometimes: правило применяется, только если свойство присутствует (в форме)
    public ?int $sample_order = 0;

    // Сохраняем типизацию ?float
    #[Rule('nullable', 'sometimes', 'numeric')]
    public ?float $case_cost = null;

    // Сохраняем типизацию ?float
    #[Rule('nullable', 'sometimes', 'numeric')]
    public ?float $case_profit = null;

    #[Rule('nullable', 'string', 'max:10000')]
    public ?string $case_description = null;


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
    public function setCaseProfit($value)
    {
        $this->case_profit = is_numeric($value) ? (float) $value : null;
    }
    public function render()
    {
        return view('livewire.case-form');
    }


}
