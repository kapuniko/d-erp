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
        Schema::create('artefacts_cases', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->string('type')->default('in_calendar'); //or sample
            $table->text('calendar_date')->nullable();
            $table->text('calendar_time')->nullable();
            $table->text('sample_order')->nullable();
            $table->float('case_cost')->nullable();
            $table->float('case_profit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artefacts_cases');
    }
};
