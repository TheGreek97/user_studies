<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'ignored_warning',
        'shown_warning',
        'expertise_score',
        'study_completed',
        'prolific_id',
        'demographics_completed',
        'bfi_completed',
        'stp_completed',
        'teique_completed',
        'questionnaires_completed',
        'pre_training_completed',
        'training_completed',
        'post_training_completed',
        'training_reaction_completed',
        'expelled',
        'given_consent',
        'training_personalization',
        'training_length'
    ];

    protected $guarded = [
        'warning_type',  // useless for branch
        'show_explanation',  // useless for branch
        'show_details',  // useless for branch
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function questionnaire()
    {
        return $this->hasMany(UserEmailQuestionnaire::class);
    }

    public function training()
    {
        return $this->hasOne(Training::class);
    }

    public function followUpQuestionnaire()
    {
        return $this->hasOne(FollowUpQuestionnaire::class);
    }

    public function logs()
    {
        return $this->hasMany(ActivityLogs::class);
    }

    public function userProfile()
    {
        $profile = $this->hasOne(UserQuestionnaireScale::class)->first();
        return $profile;
    }

    public function getUserProfilePrompt(){
        $profile = $this->userProfile();
        return "The training material must be tailored to the user’s profile, which is defined by the following characteristics:
- Personality traits, measured under the Big five factors from 1 (low) to 5 (high):
Extraversion = $profile->bfi_extraversion
Agreeableness = $profile->bfi_agreeableness
Conscientiousness = $profile->bfi_conscientiousness
Negative emotionality = $profile->bfi_negative_emotionality
Open mindedness = $profile->bfi_open_mindedness
- Emotional intelligence factors, measured on a scale from 1 (low) to 7 (high):
Total Trait Emotional Intelligence = $profile->tei_total_tei
Well-being = $profile->tei_well_being
Self-Control = $profile->tei_self_control
Emotionality = $profile->tei_emotionality
Sociability = $profile->tei_sociability
- Persuasion susceptibility factors, measured on a scale from 1 (low) to 7 (high):
Lack of premeditation = $profile->stp_lack_of_premeditation
Need for consistency = $profile->stp_need_for_consistency
Sensation seeking = $profile->stp_sensation_seeking
Lack of self-control = $profile->stp_lack_of_self_control
Social influence = $profile->stp_social_influence
Need for avoidance of similarity = $profile->stp_need_for_avoidance_of_similarity
Risk preferences = $profile->stp_risk_preferences
Need for cognition = $profile->stp_need_for_cognition
Need for uniqueness = $profile->stp_need_for_uniqueness
";
    }


    public function getUserMainTraits()
    {
        $profile = $this->userProfile();
         // Define all traits and their scales
        $traits = [
            // Big Five (1-5 scale)
            'Extraversion' => ['value' => $profile->bfi_extraversion, 'scale' => 4],
            'Agreeableness' => ['value' => $profile->bfi_agreeableness, 'scale' => 4],
            'Conscientiousness' => ['value' => $profile->bfi_conscientiousness, 'scale' => 4],
            'Negative emotionality' => ['value' => $profile->bfi_negative_emotionality, 'scale' => 4],
            'Open mindedness' => ['value' => $profile->bfi_open_mindedness, 'scale' => 4],

            // Emotional intelligence (1-7 scale)
            'Total Trait Emotional Intelligence' => ['value' => $profile->tei_total_tei, 'scale' => 6],
            'Well-being' => ['value' => $profile->tei_well_being, 'scale' => 6],
            'Self-Control' => ['value' => $profile->tei_self_control, 'scale' => 6],
            'Emotionality' => ['value' => $profile->tei_emotionality, 'scale' => 6],
            'Sociability' => ['value' => $profile->sociability, 'scale' => 6],

            // Persuasion susceptibility (1-7 scale)
            'Lack of premeditation' => ['value' => $profile->stp_lack_of_premeditation, 'scale' => 6],
            'Need for consistency' => ['value' => $profile->stp_need_for_consistency, 'scale' => 6],
            'Sensation seeking' => ['value' => $profile->stp_sensation_seeking, 'scale' => 6],
            'Lack of self-control' => ['value' => $profile->stp_lack_of_self_control, 'scale' => 6],
            'Social influence' => ['value' => $profile->stp_social_influence, 'scale' => 6],
            'Need for avoidance of similarity' => ['value' => $profile->stp_need_for_avoidance_of_similarity, 'scale' => 6],
            'Risk preferences' => ['value' => $profile->stp_risk_preferences, 'scale' => 6],
            'Need for cognition' => ['value' => $profile->need_for_cognition, 'scale' => 6],
            'Need for uniqueness' => ['value' => $profile->need_for_uniqueness, 'scale' => 6],
        ];

        $rankedTraits = [];

        // Normalize values and calculate extremity & polarity
        foreach ($traits as $trait => $data) {
            $normalized = ($data['value'] - 1) / $data['scale']; // Normalize to 0-1
            $extremity = abs($normalized - 0.5); // Distance from the midpoint (0.5)

            // Determine polarity
            if ($normalized >= 0.65) {
                $polarity = 'high';
            } elseif ($normalized <= 0.35) {
                $polarity = 'low';
            } else {
                continue; // Skip traits that are in the middle range
            }

            $rankedTraits[$trait] = [
                'extremity' => $extremity,
                'polarity' => $polarity
            ];
        }

        // Sort by extremity in descending order
        usort($rankedTraits, function ($a, $b) {
            return $b['extremity'] <=> $a['extremity'];
        });

        return $rankedTraits;
    }

    public static function getExistingProlificParticipant($prolificId)
    {
        if ($prolificId == null) {
            return null;
        }
        // Query the database to find the user by the prolific_id column
        return User::where('prolific_id', $prolificId)
            ->first();
    }
}
