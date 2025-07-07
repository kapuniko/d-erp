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
        Schema::table('artefact_case', function (Blueprint $table) {
            $table->bigInteger('coin_id')->default(1);
            $table->float('price_in_case')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artefact_case', function (Blueprint $table) {
            //
        });
    }
};
