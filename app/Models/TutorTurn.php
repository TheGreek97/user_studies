<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Log of one Module 4 tutor turn (dialog or closing): outcome, validation, fallback, tokens and latency. */
class TutorTurn extends Model
{
    protected $fillable = [
        'user_id',
        'training_id',
        'tutor_session_id',
        'email_id',
        'position',
        'help_level',
        'turn_index',
        'turn_type',
        'state',
        'user_message',
        'user_choice',
        'tutor_message',
        'raw_output',
        'validated_output',
        'accepted_clue_ids',
        'used_fallback',
        'validator_errors',
        'model',
        'input_tokens',
        'output_tokens',
        'latency_ms',
    ];

    protected $casts = [
        'raw_output' => 'array',
        'validated_output' => 'array',
        'accepted_clue_ids' => 'array',
        'validator_errors' => 'array',
        'used_fallback' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
