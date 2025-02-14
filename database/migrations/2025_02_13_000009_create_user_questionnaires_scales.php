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
        Schema::create('user_questionnaires_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('lack_of_premeditation', 8, 7)->nullable();
            $table->decimal('need_for_consistency', 8, 7)->nullable();
            $table->decimal('sensation_seeking', 8, 7)->nullable();
            $table->decimal('lack_of_self_control', 8, 7)->nullable();
            $table->decimal('social_influence', 8, 7)->nullable();
            $table->decimal('need_for_avoidance_of_similarity', 8, 7)->nullable();
            $table->decimal('risk_preferences', 8, 7)->nullable();
            $table->decimal('positive_attitudes_towards_advertising', 8, 7)->nullable();
            $table->decimal('need_for_cognition', 8, 7)->nullable();
            $table->decimal('need_for_uniqueness', 8, 7)->nullable();
            $table->decimal('extraversion', 8, 7)->nullable();
            $table->decimal('agreeableness', 8, 7)->nullable();
            $table->decimal('conscientiousness', 8, 7)->nullable();
            $table->decimal('negative_emotionality', 8, 7)->nullable();
            $table->decimal('open_mindedness', 8, 7)->nullable();
            $table->decimal('total_tei', 8, 7)->nullable();
            $table->decimal('well_being', 8, 7)->nullable();
            $table->decimal('self_control', 8, 7)->nullable();
            $table->decimal('emotionality', 8, 7)->nullable();
            $table->decimal('sociability', 8, 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tei_que-sfs');
    }
};
