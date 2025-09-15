<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Scores & metadata (nullable until computed)
            $table->json('raw_scores')->nullable();
            $table->json('processed_scores')->nullable();
            $table->json('dimension_scores')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('derived_metrics')->nullable();
            $table->json('training_modules')->nullable();
            $table->boolean('overclaiming')->default(false);
            $table->boolean('attention_flag')->default(false);
            $table->decimal('risk_score', 5, 1)->nullable();
            $table->string('risk_level', 64)->nullable();
            // Marked when the assessment is started and completed
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
