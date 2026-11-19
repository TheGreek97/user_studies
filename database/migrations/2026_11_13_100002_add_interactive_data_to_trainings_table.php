<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structured data of an "interactive" training: the JSON blocks extracted from the generated
 * HTML (scenario header and email, defense guidelines) and the Module 4 email sequence.
 * NULL for the paper's conditions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->json('interactive_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn('interactive_data');
        });
    }
};
