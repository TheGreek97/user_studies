<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingQuizResult extends Model
{
    protected $fillable = [
        'user_id','training_id','total_questions','correct_answers','percent','details'
    ];
    protected $casts = [
        'details' => 'array',
    ];
}
