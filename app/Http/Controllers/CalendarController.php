<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CalendarEvent;
use Illuminate\Support\Carbon;

use App\Services\CalendarService;

class CalendarController extends Controller
{
    public function index(CalendarService $calendarService)
    {
        $grouped = $calendarService->getGroupedEvents();

        return view('calendar.index', compact('grouped'));
    }

}
