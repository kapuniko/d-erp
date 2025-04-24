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
        Schema::create('artefact_case', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artefact_id')->constrained()->onDelete('cascade');
            $table->foreignId('artefacts_case_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artefact_case');
    }
};
