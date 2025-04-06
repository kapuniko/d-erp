<?php

namespace App\View\Components;

use App\Services\CalendarService;
use Illuminate\View\Component;

class CalendarYear extends Component
{
    public int $year;
    public array $grouped;

    public function __construct(int $year)
    {
        $this->year = $year;
        $this->grouped = app(CalendarService::class)->getGroupedEvents();
    }

    public function render()
    {
        return view('components.calendar-year');
    }
}
