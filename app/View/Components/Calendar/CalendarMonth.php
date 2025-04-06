<?php

namespace App\View\Components\Calendar;

use Illuminate\View\Component;

class CalendarMonth extends Component
{
    public int $year;
    public int $month;
    public string $monthName;
    public array $grouped;

    public function __construct(int $year, int $month, string $monthName, array $grouped)
    {
        $this->year = $year;
        $this->month = $month;
        $this->monthName = $monthName;
        $this->grouped = $grouped;
    }

    public function render()
    {
        return view('components.calendar.calendar-month');
    }
}

