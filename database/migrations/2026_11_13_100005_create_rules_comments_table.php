<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 5 of the interactive condition (cap4 4.3.5, prompt 5b): the ONE tutor comment on the user's personal rules.
 * One row per user and training (unique: a second paid call is impossible even if the application lock failed).
 * The rules themselves are in training_responses (module 5, rules_v1 / rules_v2).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('rules_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            // shown: valid comment shown to the user; skipped: invalid twice / provider error (revision still allowed).
            $table->string('status', 16);
            $table->text('message')->nullable();
            $table->json('covered_clue_ids')->nullable();
            $table->string('focus_clue_id', 100)->nullable();
            $table->boolean('generic_rule')->nullable();
            // Input of {{missed_clues}}: ids of the main clues missed in Module 4 (server-side, from the cards).
            $table->json('missed_clue_ids')->nullable();
            // Every attempt: raw reply, failure type, violations.
            $table->json('raw_output')->nullable();
            $table->json('validator_errors')->nullable();
            $table->string('model', 100)->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'training_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rules_comments');
    }
};
