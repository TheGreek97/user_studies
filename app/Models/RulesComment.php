<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Module 5 tutor comment on the personal rules (prompt 5b): one per user and training, shown or skipped. */
class RulesComment extends Model
{
    public const SHOWN = 'shown';
    public const SKIPPED = 'skipped';

    protected $fillable = [
        'user_id',
        'training_id',
        'status',
        'message',
        'covered_clue_ids',
        'focus_clue_id',
        'generic_rule',
        'missed_clue_ids',
        'raw_output',
        'validator_errors',
        'model',
        'input_tokens',
        'output_tokens',
        'latency_ms',
    ];

    protected $casts = [
        'covered_clue_ids' => 'array',
        'missed_clue_ids' => 'array',
        'raw_output' => 'array',
        'validator_errors' => 'array',
        'generic_rule' => 'boolean',
    ];

    public function isShown(): bool
    {
        return $this->status === self::SHOWN && $this->message !== null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
