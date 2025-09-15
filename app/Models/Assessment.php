<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'raw_scores',
        'processed_scores',
        'dimension_scores',
        'weaknesses',
        'derived_metrics',
        'training_modules',
        'overclaiming',
        'attention_flag',
        'risk_score',
        'risk_level',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'raw_scores'       => 'array',
        'processed_scores' => 'array',
        'dimension_scores' => 'array',
        'weaknesses'       => 'array',
        'derived_metrics'  => 'array',
        'training_modules' => 'array',
        'overclaiming'     => 'boolean',
        'attention_flag'   => 'boolean',
        'risk_score'       => 'float',
        'risk_level'       => 'string',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }
}
