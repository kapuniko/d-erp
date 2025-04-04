<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $dates = ['event_date', 'created_at', 'updated_at', 'repeat_until'];

    protected $fillable = [
        'event_date', 'event_time', 'repeat_until', 'interval_hours', 'amount', 'emoji'
    ];

    // Кастуем event_time в нужный формат (HH:MM) без секунд
    public function getEventTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i'); // Форматируем только до минут
    }

    // Кастуем event_date в формат Y-m-d
    public function getEventDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }

    // Кастуем эмодзи
    public function getEmojiAttribute($value)
    {
        return $value; // Просто возвращаем эмодзи
    }

    // Получаем повторяющиеся события
    public function getRepeatedInstances(): array
    {
        $instances = [];

        // Формируем строку для Carbon
        $formattedDateTime = $this->event_date . ' ' . substr($this->event_time, 0, 5); // Отрезаем секунды, если они есть

//        \Log::info('Сформированная строка времени: ' . $formattedDateTime); // Логируем строку для отладки

        $start = null;

        try {
            // Попытка преобразования строки в объект Carbon
            $start = Carbon::createFromFormat('Y-m-d H:i', $formattedDateTime);
//             \Log::info('Carbon объект создан: ' . $start); // Логируем успешное создание объекта
            $instances[] = $start;
        } catch (\Exception $e) {
            // Логирование ошибки и вывода строки
//            \Log::error('Ошибка при парсинге даты и времени: ' . $e->getMessage());
//            \Log::error('Строка, которая вызвала ошибку: ' . $formattedDateTime);
        }

        // Проверка, была ли успешно создана переменная $start
        if ($start && $this->repeat_until && $this->interval_hours) {
            $end = Carbon::parse($this->repeat_until)->endOfDay();
            $next = $start->copy()->addHours($this->interval_hours);

            while ($next <= $end) {
                $instances[] = $next->copy();
                $next->addHours($this->interval_hours);
            }
        }

        return $instances;
    }
}
