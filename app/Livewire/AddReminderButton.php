<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Reminder;

class AddReminderButton extends Component
{
    public $calendarEventId;
    public $remindAt;
    public $emoji;
    public $name;
    public $eventTime;
    public $status = 'none';  //none, pending, sent
    public $eventKey;

    public function mount($calendarEventId, $remindAt, $emoji, $name, $eventTime, $initialStatus, $eventKey)
    {
        $this->calendarEventId = $calendarEventId;
        $this->remindAt = $remindAt;
        $this->emoji = $emoji;
        $this->name = $name;
        $this->status = $initialStatus;
        $this->eventTime = $eventTime;
        $this->eventKey = $eventKey;
    }

    public function toggleReminder()
    {
        $user = Auth::user();

        if ($this->status === 'none') {
            Reminder::create([
                'user_id' => $user->id,
                'chat_id' => $user->telegram_id,
                'message' => 'Через 10 минут, в ' . $this->eventTime .' - '. $this->emoji . ' ' . $this->name,
                'remind_at' => $this->remindAt,
                'event_key' => $this->eventKey,
                'sent' => false,
                'calendar_event_id' => $this->calendarEventId,
            ]);
            $this->status = 'pending';

        } else {
            Reminder::where('user_id', $user->id)
                ->where('calendar_event_id', $this->calendarEventId)
                ->where('event_key', $this->eventKey)
                ->delete();

            $this->status = 'none';
        }
    }

    public function render()
    {
        return view('livewire.add-reminder-button');
    }
}
