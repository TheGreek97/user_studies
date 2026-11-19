<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Written activity of a training module (1, 4 or 5): one row per user + module + item, overwritten on resubmit. */
class TrainingResponse extends Model
{
    protected $fillable = [
        'user_id',
        'training_id',
        'module',
        'item_key',
        'response_text',
        'response_json',
    ];

    protected $casts = [
        'response_json' => 'array',
        'module' => 'integer',
    ];

    /** Upsert on (user_id, module, item_key). */
    public static function record(int $userId, ?int $trainingId, int $module, string $itemKey, ?string $text, ?array $json = null): self
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'module' => $module, 'item_key' => $itemKey],
            ['training_id' => $trainingId, 'response_text' => $text, 'response_json' => $json],
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
