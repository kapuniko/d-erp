<?php

namespace App\Services;

use App\Models\CalendarEvent;
use Carbon\Carbon;

class CalendarService
{
    public function getGroupedEvents(?int $userId = null): array
    {
        //$events = CalendarEvent::all();
        $events = CalendarEvent::visibleForUser($userId)->get();
        $grouped = [];

//        \Log::info("All Events: ", $events->toArray());

        // Для каждого события
        foreach ($events as $event) {
            $instances = $event->getRepeatedInstances();

//            \Log::info("Instances for Event " . $event->id . ": ", $instances);

            // Для повторяющихся событий
            if ($event->display_type->value === 'repeat' || $event->display_type->value === 'single') {
                foreach ($instances as $instance) {
                    $key = $instance->format('Y-m-d');
                    $grouped[$key] = $grouped[$key] ?? collect();
                    $grouped[$key]->push((object)[
                        'emoji' => $event->emoji,
                        'event_time' => $instance->format('H:i'),
                        'amount' => $event->amount,
                        'display_type' => $event->display_type,
                        'name' => $event->name,
                        'id' => $event->id,
                        'event_date' => $event->event_date,
                        'user_id' => $event->user_id,
                        'event_end_date' => $event->event_end_date,
                        'repeat_until' => $event->repeat_until,
                        'interval_hours' => $event->interval_hours,
                        'calendar_datetime' => $instance->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            // Для многодневных событий
            if ($event->display_type->value === 'range') {
                // Получаем все дни между start и end
                $multiDayInstances = $event->getMultiDayInstances();

                foreach ($multiDayInstances as $multiDayInstance) {
                    $key = $multiDayInstance->format('Y-m-d');

                    // Добавляем только если этот день еще не был добавлен (чтобы избежать дублирования)
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = collect();
                    }

                    $grouped[$key]->push((object)[
                        'emoji' => $event->emoji,
                        'event_time' => $multiDayInstance->format('H:i'),
                        'amount' => $event->amount,
                        'display_type' => $event->display_type,
                        'is_multiday' => true, // Для использования в шаблоне
                        'name' => $event->name,
                        'id' => $event->id,
                        'event_date' => $event->event_date,
                        'user_id' => $event->user_id,
                        'event_end_date' => $event->event_end_date,
                        'repeat_until' => $event->repeat_until,
                        'interval_hours' => $event->interval_hours,
                    ]);
                }
            }
        }

//        \Log::info("Grouped Events: ", $grouped);
        return $grouped;
    }


}
