<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 4 tutor chat (TutorChat Livewire component):
 * - tutor_sessions: one running conversation per user and training (email sequence + current DialogSession state);
 * - tutor_turns: one row per tutor turn (prompt outcome, validation, fallback, tokens, latency) for the analysis;
 * - training_responses: written activities of modules 1/4/5, one row per user + module + item (upserted).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('tutor_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Always set: TutorConversation refuses (409) users without a training.
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            // Email sequence: trainings.interactive_data.m4.sequence when present (seed null), otherwise drawn here
            // with DialogSession::drawSequence (reproducible from the seed).
            $table->unsignedInteger('seed')->nullable();
            $table->json('sequence');
            $table->unsignedTinyInteger('legitimate_position');
            // 1-based position of the email currently on screen.
            $table->unsignedTinyInteger('position')->default(1);
            // DialogSession::toArray() of the current email (card referenced by id only).
            $table->json('state')->nullable();
            // DialogSession::toArray() of the emails already closed, in order (event log, Tabella 10).
            $table->json('closed_states')->nullable();
            // Main clues missed so far: input {{previous_missed_clues}} of the next emails.
            $table->json('missed_clues')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'training_id']);
        });

        Schema::create('tutor_turns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('training_id')->nullable()->constrained('trainings')->nullOnDelete();
            $table->foreignId('tutor_session_id')->nullable()->constrained('tutor_sessions')->cascadeOnDelete();
            $table->string('email_id', 64);
            $table->unsignedTinyInteger('position');
            $table->string('help_level', 16);
            // 1-based index of the tutor turn on this email.
            $table->unsignedSmallInteger('turn_index');
            $table->string('turn_type', 16); // dialog | closing
            $table->string('state', 32); // Phase after the turn
            $table->text('user_message')->nullable();
            $table->string('user_choice', 16)->nullable();
            $table->text('tutor_message')->nullable(); // null when the dialog message was suppressed
            $table->json('raw_output')->nullable(); // every attempt: raw reply, failure, violations
            $table->json('validated_output')->nullable(); // TutorOutput used (model or fallback)
            // Ids of validated_output.recognized_clue_ids that the platform accepted (ClueEvidence: supported by the
            // user's own messages); the difference with the model's claim is what the server rejected.
            $table->json('accepted_clue_ids')->nullable();
            $table->boolean('used_fallback')->default(false);
            $table->json('validator_errors')->nullable();
            $table->string('model', 100)->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'email_id']);
        });

        Schema::create('training_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('training_id')->nullable()->constrained('trainings')->nullOnDelete();
            $table->unsignedTinyInteger('module');
            $table->string('item_key', 100);
            $table->text('response_text')->nullable();
            $table->json('response_json')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'module', 'item_key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('training_responses');
        Schema::dropIfExists('tutor_turns');
        Schema::dropIfExists('tutor_sessions');
    }
};
