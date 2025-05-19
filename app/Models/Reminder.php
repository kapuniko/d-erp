<?php

declare(strict_types=1);

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
		'user_id',
		'chat_id',
		'message',
		'remind_at',
		'sent',
		'calendar_event_id',
        'event_key'
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'event_key' => 'datetime',
        'sent' => 'boolean',
    ];
}
