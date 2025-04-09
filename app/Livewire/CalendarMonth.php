<?php

namespace App\Livewire;

use App\Services\CalendarService;
use Carbon\Carbon;
use Livewire\Component;

class CalendarMonth extends Component
{
    public $year;
    public $month;
    public $monthName;
    public $groupedEvents;

    public function mount($year = 2025, $month = 4)
    {
        $this->year = $year;
        $this->month = $month;
        $this->monthName = Carbon::create($year, $month, 1)->monthName;
        $this->groupedEvents = (new CalendarService())->getGroupedEvents();
    }

    // Метод для изменения месяца
    public function changeMonth($direction)
    {
        if ($direction === 'next') {
            $this->month++;
            if ($this->month > 12) {
                $this->month = 1;
                $this->year++;
            }
        } elseif ($direction === 'previous') {
            $this->month--;
            if ($this->month < 1) {
                $this->month = 12;
                $this->year--;
            }
        }

        // Обновляем события после изменения месяца
        $this->monthName = Carbon::create($this->year, $this->month, 1)->monthName;
        $this->groupedEvents = (new CalendarService())->getGroupedEvents();
    }

    public function render()
    {
        return view('livewire.calendar-month');
    }
}
