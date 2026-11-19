<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Running Module 4 tutor conversation of one user (see App\Services\Tutor\TutorConversation).
 * `state` holds DialogSession::toArray() of the current email; the solution card is never stored here.
 */
class TutorSession extends Model
{
    protected $fillable = [
        'user_id',
        'training_id',
        'seed',
        'sequence',
        'legitimate_position',
        'position',
        'state',
        'closed_states',
        'missed_clues',
        'completed_at',
    ];

    protected $casts = [
        'sequence' => 'array',
        'state' => 'array',
        'closed_states' => 'array',
        'missed_clues' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function turns()
    {
        return $this->hasMany(TutorTurn::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }
}
