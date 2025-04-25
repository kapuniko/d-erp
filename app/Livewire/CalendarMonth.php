<?php

namespace App\Livewire;

use App\Models\Artefact;
use App\Models\ArtefactsCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Services\CalendarService;
use Carbon\Carbon;

class CalendarMonth extends Component
{
    public int $year;
    public int $month;
    public array $grouped = [];
    public array $monthlyEvents = [];

    public Collection $artefacts;
    public Collection $artefactsCases;

    public function mount($year, $month)
    {
        $this->year = $year;
        $this->month = $month;

        $calendarService = new CalendarService();
        $this->grouped = $calendarService->getGroupedEvents(Auth::id());

        // Собираем события для отображения в сайдбаре
        $this->monthlyEvents = $this->getEventsForMonth();

        $this->artefacts = Artefact::where('user_id', auth()->id())
            ->orWhereNull('user_id')
            ->get();

        $this->artefactsCases = ArtefactsCase::where('user_id', auth()->id())
            ->orWhereNull('user_id')
            ->get();
    }

    public function getEventsForMonth(): array
    {
        $start = Carbon::create($this->year, $this->month)->startOfMonth();
        $end = Carbon::create($this->year, $this->month)->endOfMonth();
        $events = [];

        foreach ($this->grouped as $date => $dayEvents) {
            $dateObj = Carbon::parse($date);
            if ($dateObj->between($start, $end)) {
                foreach ($dayEvents as $event) {
                    $events[] = $event;
                }
            }
        }

        return $events;
    }

    public function changeMonth($direction)
    {
        if ($direction === 'previous') {
            $date = Carbon::create($this->year, $this->month)->subMonth();
        } else {
            $date = Carbon::create($this->year, $this->month)->addMonth();
        }

        $this->year = $date->year;
        $this->month = $date->month;

        $calendarService = new CalendarService();
        $this->grouped = $calendarService->getGroupedEvents(Auth::id());
        $this->monthlyEvents = $this->getEventsForMonth();

    }


    public function render()
    {
        $monthName = Carbon::create($this->year, $this->month)->translatedFormat('F');
        return view('livewire.calendar-month', [
            'monthName' => $monthName,
        ]);
    }
}
