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
        Schema::create('tax_amounts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('pers_level');
            $table->integer('gold_amount_month');
            $table->integer('crystals_amount_month');
            $table->integer('pages_amount_month');
            $table->integer('gold_amount_year');
            $table->integer('crystals_amount_year');
            $table->integer('pages_amount_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_amounts');
    }
};
