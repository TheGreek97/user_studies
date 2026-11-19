<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generation outcome of a training module: failure state (shown on the waiting page,
 * cleared by the retry route) and the model + token usage of a successful generation
 * (per-participant cost estimate).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->timestamp('generation_failed_at')->nullable();
            $table->string('generation_error', 500)->nullable();
            $table->string('llm_model')->nullable();
            $table->json('llm_usage')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn(['generation_failed_at', 'generation_error', 'llm_model', 'llm_usage']);
        });
    }
};
