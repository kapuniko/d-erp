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
        Schema::create('treasury_logs', function (Blueprint $table) {
            $table->id();
            $table->datetime('date');
            $table->text('name');
            $table->text('type');
            $table->text('object');
            $table->decimal('quantity', 18, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treasury_logs');
    }
};
