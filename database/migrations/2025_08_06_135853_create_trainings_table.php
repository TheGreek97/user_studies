<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();

            // FK to users (required)
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // FK to assessments (required)
            $table->foreignId('assessment_id')
                ->constrained('assessments')
                ->cascadeOnDelete();

            // Training content
            $table->longText('introduction')->nullable();
            $table->longText('scenario')->nullable();
            $table->longText('defense_strategies')->nullable();
            $table->longText('exercises')->nullable();
            $table->longText('conclusions')->nullable();

            $table->text('intro_content')->nullable();
            $table->text('consolidation_content')->nullable();
            $table->text('checklist_content')->nullable();
            $table->text('nudges_content')->nullable();
            $table->json('modules')->nullable();

            // Status flags / timestamps
            $table->boolean('generated')->default(false);
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
