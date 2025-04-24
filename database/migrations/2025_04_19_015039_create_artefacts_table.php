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
        Schema::create('artefacts', function (Blueprint $table) {
            $table->id();
            $table->string('game_id')->nullable(); //id из двара
            $table->integer('user_id')->nullable();
            $table->string('name');
            $table->string('type'); //pot & buf & art
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration_sec')->nullable();
            $table->integer('level')->nullable();
            $table->string('group')->nullable();
            $table->float('price')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('effects');
    }
};
