<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuestionnaireScale extends Model
{
    use HasFactory;

    protected $table = 'user_questionnaires_scales'; 

    protected $fillable = [
        'user_id',
        'lack_of_premeditation',
        'need_for_consistency',
        'sensation_seeking',
        'lack_of_self_control',
        'social_influence',
        'need_for_avoidance_of_similarity',
        'risk_preferences',
        'positive_attitudes_towards_advertising',
        'need_for_cognition',
        'need_for_uniqueness',
        'extraversion',
        'agreeableness',
        'conscientiousness',
        'negative_emotionality',
        'open_mindedness',
        'total_tei',
        'well_being',
        'self_control',
        'emotionality',
        'sociability',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
