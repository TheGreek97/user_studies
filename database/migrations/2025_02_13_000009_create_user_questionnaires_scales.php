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
            $table->decimal('stp_lack_of_premeditation', 8, 7)->nullable();
            $table->decimal('stp_need_for_consistency', 8, 7)->nullable();
            $table->decimal('stp_sensation_seeking', 8, 7)->nullable();
            $table->decimal('stp_lack_of_self_control', 8, 7)->nullable();
            $table->decimal('stp_social_influence', 8, 7)->nullable();
            $table->decimal('stp_need_for_avoidance_of_similarity', 8, 7)->nullable();
            $table->decimal('stp_risk_preferences', 8, 7)->nullable();
            $table->decimal('stp_positive_attitudes_towards_advertising', 8, 7)->nullable();
            $table->decimal('stp_need_for_cognition', 8, 7)->nullable();
            $table->decimal('stp_need_for_uniqueness', 8, 7)->nullable();
            $table->decimal('bfi_extraversion', 8, 7)->nullable();
            $table->decimal('bfi_agreeableness', 8, 7)->nullable();
            $table->decimal('bfi_conscientiousness', 8, 7)->nullable();
            $table->decimal('bfi_negative_emotionality', 8, 7)->nullable();
            $table->decimal('bfi_open_mindedness', 8, 7)->nullable();
            $table->decimal('tei_total_tei', 8, 7)->nullable();
            $table->decimal('tei_well_being', 8, 7)->nullable();
            $table->decimal('tei_self_control', 8, 7)->nullable();
            $table->decimal('tei_emotionality', 8, 7)->nullable();
            $table->decimal('tei_sociability', 8, 7)->nullable();
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
