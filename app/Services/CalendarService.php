<?php

namespace App\Services;

use App\Models\CalendarEvent;
use Illuminate\Support\Facades\Log;

class CalendarService
{
    public function getGroupedEvents(): array
    {
        $events = CalendarEvent::all();

        $grouped = [];

        foreach ($events as $event) {
            $instances = $event->getRepeatedInstances();

            // Обработка многодневных событий
            if ($event->display_type === 'range' && $event->event_end_date) {
                $startDate = \Carbon\Carbon::parse($event->event_date);
                $endDate = \Carbon\Carbon::parse($event->event_end_date);

                // Добавляем событие на все дни от event_date до event_end_date
                while ($startDate <= $endDate) {
                    $key = $startDate->format('n-j'); // Формируем ключ для дня

                    // Логируем информацию
                    Log::info("Добавление события на день: " . $startDate->format('Y-m-d'));

                    // Инициализируем массив для этого дня, если он еще не существует
                    $grouped[$key] = $grouped[$key] ?? collect();

                    // Добавляем событие для каждого дня
                    $grouped[$key]->push((object)[
                        'emoji' => $event->emoji,
                        'event_time' => '', // Для многодневных событий не указываем время
                        'amount' => $event->amount,
                        'event_end_date' => $event->event_end_date,
                        'display_type' => $event->display_type,
                        'event_date' => $event->event_date,
                    ]);

                    $startDate->addDay(); // Переходим к следующему дню
                }
            }

            // Обрабатываем повторяющиеся события
            foreach ($instances as $instance) {
                $key = $instance->format('n-j');
                $grouped[$key] = $grouped[$key] ?? collect();

                $grouped[$key]->push((object)[
                    'emoji' => $event->emoji,
                    'event_time' => $instance->format('H:i'),
                    'amount' => $event->amount,
                    'event_end_date' => $event->event_end_date,
                    'display_type' => $event->display_type,
                    'event_date' => $event->event_date,
                ]);
            }
        }

        return $grouped;
    }
}
