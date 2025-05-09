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
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'sent' => 'boolean',
    ];
}
