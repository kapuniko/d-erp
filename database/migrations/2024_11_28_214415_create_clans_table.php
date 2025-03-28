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
        Schema::create('clans', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->integer('owner');
            $table->integer('talent_1')->nullable();
            $table->integer('talent_2')->nullable();
            $table->integer('talent_3')->nullable();
            $table->integer('talent_4')->nullable();
            $table->integer('talent_5')->nullable();
            $table->integer('talent_6')->nullable();
            $table->integer('talent_7')->nullable();
            $table->integer('talent_8')->nullable();
            $table->integer('talent_9')->nullable();
            $table->integer('talent_10')->nullable();
            $table->integer('talent_11')->nullable();
            $table->integer('talent_12')->nullable();
            $table->integer('talent_13')->nullable();
            $table->integer('talent_14')->nullable();
            $table->integer('talent_15')->nullable();
            $table->integer('talent_16')->nullable();
            $table->integer('talent_17')->nullable();
            $table->integer('talent_18')->nullable();
            $table->integer('talent_19')->nullable();
            $table->integer('talent_20')->nullable();
            $table->integer('talent_21')->nullable();
            $table->integer('talent_22')->nullable();
            $table->integer('talent_23')->nullable();
            $table->integer('talent_24')->nullable();
            $table->integer('talent_25')->nullable();
            $table->integer('talent_26')->nullable();
            $table->integer('talent_27')->nullable();
            $table->integer('talent_28')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clans');
    }
};
