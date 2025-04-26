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
            $table->unsignedInteger('artefact_in_case_count')->default(1)->after('artefacts_case_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artefact_case', function (Blueprint $table) {
            $table->dropColumn('artefact_in_case_count');
        });
    }
};
