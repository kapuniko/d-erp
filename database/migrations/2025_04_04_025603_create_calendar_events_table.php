<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('emoji');
            $table->date('event_date');       // Дата первого события
            $table->time('event_time');       // Время
            $table->date('repeat_until')->nullable();   // Дата конца повторения
            $table->integer('interval_hours')->nullable(); // Интервал в часах
            $table->integer('amount')->nullable(); // может быть положительным или отрицательным
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
