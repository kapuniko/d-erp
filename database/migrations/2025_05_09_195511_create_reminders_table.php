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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id'); // кому отправлять
            $table->string('chat_id'); // ID в Telegram
            $table->text('message');
            $table->timestamp('remind_at'); // когда отправить
            $table->boolean('sent')->default(false); // флаг, отправлено или нет
            $table->bigInteger('calendar_event_id')->nullable(); // связанное событие
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
