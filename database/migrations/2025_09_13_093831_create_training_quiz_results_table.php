<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('total_questions');
            $table->unsignedSmallInteger('correct_answers');
            $table->unsignedTinyInteger('percent'); // 0–100
            $table->json('details')->nullable();    // [{index, chosen, correct}]
            $table->timestamps();
            $table->unique(['user_id','training_id']); // one final result per training
        });
    }
    public function down(): void {
        Schema::dropIfExists('training_quiz_results');
    }
};
