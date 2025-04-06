<?php

namespace App\View\Components\Calendar;

use Illuminate\View\Component;

class Calendar extends Component
{
    public $grouped;

    public function __construct($grouped)
    {
        $this->grouped = $grouped;
    }

    public function render()
    {
        return view('components.calendar.calendar');
    }
}
