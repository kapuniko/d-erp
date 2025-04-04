<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Event extends Component
{
    public $event;
    public function __construct($event)
    {
        $this->event = $event;
    }

    public function render(): View|Closure|string
    {
        return view('components.event');
    }
}
