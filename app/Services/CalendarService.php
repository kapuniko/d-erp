<?php

namespace App\Services;

use App\Models\CalendarEvent;

class CalendarService
{
    public function getGroupedEvents(): array
    {
        $events = CalendarEvent::all();
        $grouped = [];

        foreach ($events as $event) {
            $instances = $event->getRepeatedInstances();
            foreach ($instances as $instance) {
                $key = $instance->format('n-j');
                $grouped[$key] = $grouped[$key] ?? collect();
                $grouped[$key]->push((object)[
                    'emoji' => $event->emoji,
                    'event_time' => $instance->format('H:i'),
                    'amount' => $event->amount,
                ]);
            }
        }

        return $grouped;
    }
}
