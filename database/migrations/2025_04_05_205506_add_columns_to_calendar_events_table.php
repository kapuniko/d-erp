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
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->date('event_end_date')->nullable(); // Дата окончания события
            $table->boolean('is_all_day')->default(false); // Событие на весь день?
            $table->string('display_type')->nullable(); // Например: 'single', 'repeat', 'range' — для визуализации
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('event_end_date');
             $table->dropColumn('is_all_day');
             $table->dropColumn('display_type');
        });
    }
};
