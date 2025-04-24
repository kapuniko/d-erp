<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ArtefactsCase; // Убедитесь, что модель импортирована
use Illuminate\Support\Facades\Auth; // Убедитесь, что Auth импортирован

class CaseForm extends Component
{
    public string $name = '';
    public string $type = 'in_calendar';
    public string $calendar_date = '';
    public string $calendar_time = '';
    public int $sample_order = 0;
    public ?float $case_cost = null;
    public ?float $case_profit = null;

    // Удобно добавить переменную для идентификации, что компонент используется в модалке,
    // чтобы, например, диспатчить событие закрытия модалки после сохранения.
    // public bool $inModal = true; // Опционально, если нужно отдельное поведение

    protected function rules()
    {
        // Базовые правила, которые всегда применяются
        $baseRules = [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:in_calendar,sample',
            'sample_order' => 'nullable|sometimes|integer',
            'case_cost' => 'nullable|sometimes|numeric',
            'case_profit' => 'nullable|sometimes|numeric',
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

    // Метод mount теперь получает дату из Alpine.js через параметр
    public function mount()
    {
        // Основная инициализация будет происходить через loadFormData, вызванный из x-init
        // Можно сбросить форму здесь, чтобы убедиться, что она чистая при первом монтировании
         $this->reset();

    }

    // Метод для загрузки данных из Alpine (вызывается через x-init)
    public function loadFormData(array $data)
    {
        \Log::info('CaseForm::loadFormData received data:', $data);

        // --- ПОПРОБУЙТЕ ВРЕМЕННО ЗАКОММЕНТИРОВАТЬ ЭТУ СТРОКУ ДЛЯ ТЕСТИРОВАНИЯ ---
        // $this->reset(); // Сброс ДО установки новых данных

        // Устанавливаем тип и дату из переданных данных
        // Убедитесь, что ключи 'type' и 'date' существуют в $data
        $this->type = $data['type'] ?? $this->type; // Сохраняем текущий тип, если новый не передан или null
        $this->calendar_date = $data['date'] ?? ''; // Пустая строка для date input, если null передан

        // Лог после присвоения
        \Log::info('CaseForm::loadFormData properties after assignment:', [
            'type' => $this->type,
            'calendar_date' => $this->calendar_date,
            'name' => $this->name // Проверьте, что другие поля тоже в ожидаемом состоянии (они не должны меняться этим методом)
        ]);

        // Возможно, нужно добавить логику сброса для некоторых полей, если они специфичны для типа.
        // Например, если перешли с 'sample' на 'in_calendar', sample_order должен быть сброшен.
        // If ($this->type === 'in_calendar') { $this->sample_order = 0; }
    }
    public function store()
    {
        $this->validate();

        // Убедитесь, что ArtefactsCase модель существует и доступна
        ArtefactsCase::create([
            'name' => $this->name,
            'type' => $this->type,
            'calendar_date' => $this->calendar_date ?: null,
            'calendar_time' => $this->calendar_time ?: null,
            // Убедитесь, что nullable поля сохраняются как null, если они пустые.
            // Casts в модели могут помочь, или можно делать тернарные операторы здесь
            'sample_order' => $this->sample_order ?: null, // Если 0 или null, сохранить null
            'case_cost' => $this->case_cost ?: null, // Если 0.0 или null, сохранить null
            'case_profit' => $this->case_profit ?: null, // Если 0.0 или null, сохранить null
            'user_id' => Auth::id(), // Используйте Auth фасад для получения ID текущего пользователя
        ]);

        // Очистить форму для следующего ввода
        $this->reset();
        // Установить обратно дату, если форма сбрасывается и модалка остается открытой
        // Если модалка закрывается после сохранения, этот шаг не так важен.
        // $this->calendar_date = $date ?? now()->toDateString(); // <-- Возможно, не нужно, если модалка закрывается

        // Отправить событие. Слушатель этого события в Blade или другом компоненте
        // может закрыть модалку и/или обновить список кейсов.
        $this->dispatch('case-added');
        // Возможно, также отправить событие для закрытия модалки, если это не делает слушатель 'case-added'
        //$this->dispatch('close-modal', 'caseModal'); // Пример: отправить событие для закрытия модалки по ID

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
            // Перечислите здесь все остальные публичные свойства формы
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
