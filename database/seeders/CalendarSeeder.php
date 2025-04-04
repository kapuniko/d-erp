<?php

namespace Database\Seeders;

use App\Models\CalendarEvent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CalendarEvent::create([
            'emoji' => '🐛',
            'event_date' => '2025-04-01',
            'event_time' => '08:00',
            'repeat_until' => '2025-04-03',
            'interval_hours' => 6,
            'amount' => null,
        ]);
    }
}
