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
        'stp_lack_of_premeditation',
        'stp_need_for_consistency',
        'stp_sensation_seeking',
        'stp_lack_of_self_control',
        'stp_social_influence',
        'stp_need_for_avoidance_of_similarity',
        'stp_risk_preferences',
        'stp_positive_attitudes_towards_advertising',
        'stp_need_for_cognition',
        'stp_need_for_uniqueness',
        'bfi_extraversion',
        'bfi_agreeableness',
        'bfi_conscientiousness',
        'bfi_negative_emotionality',
        'bfi_open_mindedness',
        'tei_total_tei',
        'tei_well_being',
        'tei_self_control',
        'tei_emotionality',
        'tei_sociability',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
