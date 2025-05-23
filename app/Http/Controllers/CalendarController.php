<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CalendarEvent;
use Illuminate\Support\Carbon;

use App\Models\Reminder;


use App\Services\CalendarService;

class CalendarController extends Controller
{
    public function index(CalendarService $calendarService)
    {
        $grouped = $calendarService->getGroupedEvents();

        return view('calendar.index', compact('grouped'));
    }

    public function saveEvent(Request $request)
    {
        $data = $request->only([
            'id', 'name', 'event_date', 'event_time', 'emoji', 'amount',
            'display_type', 'event_end_date', 'interval_hours', 'repeat_until'
        ]);

        $event = $data['id']
            ? CalendarEvent::find($data['id'])
            : new CalendarEvent();

        if (!$event) return response()->json(['success' => false]);

        $event->fill($data);
        $event->user_id = auth()->id(); // или null, если общее
        $event->save();

        //удаляю все напоминалки при изменения события (тупо, но пока так)
        if ($data['id']){
            Reminder::where('user_id', $event->user_id)
                ->where('calendar_event_id', $data['id'])
                ->delete();
        }



        return response()->json(['success' => true]);
    }

}
