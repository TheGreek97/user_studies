<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

// IMPORTANT: correct class names
use App\Models\Assessment;
use App\Models\Training;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable;

    /* =========================
     * Defaults & casting
     * ========================= */

    // Default attribute values when a model instance is created
    protected $attributes = [
        'training_personalization' => 'yes',   // <- default ON
        'training_length'          => 'short', // <- keep a sane default
    ];

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
        'pre_training_completed',
        'training_completed',
        'post_training_completed',
        'training_reaction_completed',
        'expelled',
        'given_consent',
        'training_personalization',
        'training_length',
        'experience_level',
        'role',
        'sector',
    ];

    protected $guarded = [
        'warning_type',
        'show_explanation',
        'show_details',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at'        => 'datetime',
        'created_at'               => 'datetime',
        'questionnaires_completed' => 'datetime',
    ];

    protected $appends = ['profile_photo_url'];

    /* =========================
     * Relationships
     * ========================= */

    public function questionnaire()
    {
        return $this->hasMany(UserEmailQuestionnaire::class);
    }

    public function followUpQuestionnaire()
    {
        return $this->hasOne(FollowUpQuestionnaire::class);
    }

    public function logs()
    {
        return $this->hasMany(ActivityLogs::class);
    }

    // Proper Eloquent relation (do NOT call ->first() here)
    public function userProfile()
    {
        return $this->hasOne(UserQuestionnaireScale::class);
    }

    // Helper to fetch the actual record (keeps your current calling style clean)
    public function getUserProfile(): ?UserQuestionnaireScale
    {
        return $this->userProfile()->first();
    }

    public function assessment()
    {
        return $this->hasMany(Assessment::class);
    }

    // Allow MULTIPLE trainings
    public function trainings()
    {
        return $this->hasMany(Training::class);
    }

    // Convenience: latest single training (use $user->training)
    public function training()
    {
        return $this->hasOne(Training::class)->latestOfMany();
    }

    /* =========================
     * Normalizers / Accessors
     * ========================= */

    // Always give a valid, normalized personalization flag
    public function getTrainingPersonalizationAttribute($value)
    {
        $v = strtolower((string)($value ?? ''));
        if ($v === '') return 'yes';
        if (!in_array($v, ['yes', 'few_shot', 'table', 'primed', 'no'], true)) return 'yes';
        return $v;
    }

    public function setTrainingPersonalizationAttribute($value)
    {
        $this->attributes['training_personalization'] =
            strtolower($value ?: 'yes');
    }

    public function getTrainingLengthAttribute($value)
    {
        $v = strtolower((string)($value ?? ''));
        return in_array($v, ['short', 'long'], true) ? $v : 'short';
    }

    public function setTrainingLengthAttribute($value)
    {
        $this->attributes['training_length'] =
            in_array(strtolower((string)$value), ['short','long'], true) ? strtolower($value) : 'short';
    }

    /* =========================
     * Personalization helpers
     * ========================= */

    public function getUserProfilePrompt(): string
    {
        $profile = $this->getUserProfile();

        // Minimal identity/context info is useful even if psych scales are missing
        $identityHeader = "NAME: {$this->name}\nROLE: {$this->role}\nSECTOR: {$this->sector}\nEXPERIENCE_LEVEL: {$this->experience_level}\n";

        // If no profile yet, return a safe, non-empty fallback so the Job can still personalize
        if (!$profile) {
            return $identityHeader .
                "NOTE: No psychometric profile available yet. Use common office-worker phishing risks; emphasize practical, role-specific guidance.";
        }

        if ($this->training_personalization == 'primed') {
            $guidelines       = $this::getPrimingGuidelines();
            $main_traits      = $this->getUserMainTraits();
            if (empty($main_traits)) {
                return $identityHeader .
                    "NOTE: Profile present but no extreme traits detected. Use balanced tone; cover authority/urgency/social-proof risks commonly seen in {$this->sector}.";
            }

            $content_guidelines = '';
            $style_guidelines   = '';
            $i = 1;

            foreach ($main_traits as $trait => $data) {
                $polarity = $data['polarity'];
                $content_guidelines .= ($guidelines[$trait]['Learning Content'][$polarity] ?? null)
                    ? ($i++ . ") " . $guidelines[$trait]['Learning Content'][$polarity] . "\n")
                    : '';
                $style_guidelines   .= ($guidelines[$trait]['Communication Style'][$polarity] ?? null)
                    ? ($i-1 . ") " . $guidelines[$trait]['Communication Style'][$polarity] . "\n")
                    : '';
            }

            return $identityHeader .
                "PERSONALIZATION – USE IMPLICITLY (do not name traits)
The training material must:
{$content_guidelines}
The communication style should be:
{$style_guidelines}";
        }

        // Default path (yes / few_shot / table)
        // Field names fixed as in your code comments
        return $identityHeader .
            "- Personality traits, measured under the Big Five factors from 1 (low) to 5 (high):
Extraversion = {$profile->bfi_extraversion}
Agreeableness = {$profile->bfi_agreeableness}
Conscientiousness = {$profile->bfi_conscientiousness}
Negative emotionality = {$profile->bfi_negative_emotionality}
Open mindedness = {$profile->bfi_open_mindedness}
- Emotional intelligence factors, measured on a scale from 1 (low) to 7 (high):
Total Trait Emotional Intelligence = {$profile->tei_total_tei}
Well-being = {$profile->tei_well_being}
Self-Control = {$profile->tei_self_control}
Emotionality = {$profile->tei_emotionality}
Sociability = {$profile->tei_sociability}
- Persuasion susceptibility factors, measured on a scale from 1 (low) to 7 (high):
Lack of premeditation = {$profile->stp_lack_of_premeditation}
Need for consistency = {$profile->stp_need_for_consistency}
Sensation seeking = {$profile->stp_sensation_seeking}
Lack of self-control = {$profile->stp_lack_of_self_control}
Social influence = {$profile->stp_social_influence}
Need for avoidance of similarity = {$profile->stp_need_for_avoidance_of_similarity}
Risk preferences = {$profile->stp_risk_preferences}
Need for cognition = {$profile->stp_need_for_cognition}
Need for uniqueness = {$profile->stp_need_for_uniqueness}";
    }

    public function getPrimingPromptScenario(): string
    {
        $guidelines  = $this::getPrimingGuidelines();
        $main_traits = $this->getUserMainTraits();
        if (empty($main_traits)) return ''; // no extremes → no priming additions

        $prompt_string = '';
        $i = 1;

        foreach ($main_traits as $trait => $data) {
            $polarity  = $data['polarity'];
            $line      = $guidelines[$trait]['Phishing Scenario'][$polarity] ?? null;
            if ($line) {
                $prompt_string .= "{$i}) {$line}\r\n";
                $i++;
            }
        }

        return $prompt_string;
    }

    private function getUserMainTraits($n = 3): array
    {
        $profile = $this->getUserProfile();
        if (!$profile) return []; // guard when no profile row exists

        $traits = [
            // Big Five (1-5 scale; range is 1..5 so span=4)
            'Extraversion'          => ['value' => $profile->bfi_extraversion,          'scale' => 4],
            'Agreeableness'         => ['value' => $profile->bfi_agreeableness,         'scale' => 4],
            'Conscientiousness'     => ['value' => $profile->bfi_conscientiousness,     'scale' => 4],
            'Negative emotionality' => ['value' => $profile->bfi_negative_emotionality, 'scale' => 4],
            'Open mindedness'       => ['value' => $profile->bfi_open_mindedness,       'scale' => 4],

            // Emotional intelligence (1-7 scale; span=6)
            'Total Trait Emotional Intelligence' => ['value' => $profile->tei_total_tei, 'scale' => 6],
            'Well-being'            => ['value' => $profile->tei_well_being,            'scale' => 6],
            'Self-Control'          => ['value' => $profile->tei_self_control,          'scale' => 6],
            'Emotionality'          => ['value' => $profile->tei_emotionality,          'scale' => 6],
            'Sociability'           => ['value' => $profile->tei_sociability,           'scale' => 6],

            // Persuasion susceptibility (1-7 scale; span=6)
            'Lack of premeditation'            => ['value' => $profile->stp_lack_of_premeditation,          'scale' => 6],
            'Need for consistency'             => ['value' => $profile->stp_need_for_consistency,           'scale' => 6],
            'Sensation seeking'                => ['value' => $profile->stp_sensation_seeking,              'scale' => 6],
            'Lack of self-control'             => ['value' => $profile->stp_lack_of_self_control,           'scale' => 6],
            'Social influence'                 => ['value' => $profile->stp_social_influence,               'scale' => 6],
            'Need for avoidance of similarity' => ['value' => $profile->stp_need_for_avoidance_of_similarity,'scale' => 6],
            'Risk preferences'                 => ['value' => $profile->stp_risk_preferences,               'scale' => 6],
            'Need for cognition'               => ['value' => $profile->stp_need_for_cognition,             'scale' => 6],
            'Need for uniqueness'              => ['value' => $profile->stp_need_for_uniqueness,            'scale' => 6],
        ];

        $rankedTraits = [];
        foreach ($traits as $trait => $data) {
            $value = (float)$data['value'];
            if ($value <= 0) continue; // guard malformed values
            $normalized = ($value - 1) / $data['scale']; // 0..1
            $extremity  = abs($normalized - 0.5);
            if ($normalized >= 0.65) {
                $polarity = 'high';
            } elseif ($normalized <= 0.35) {
                $polarity = 'low';
            } else {
                continue;
            }
            $rankedTraits[$trait] = ['extremity' => $extremity, 'polarity' => $polarity];
        }

        uasort($rankedTraits, fn($a, $b) => $b['extremity'] <=> $a['extremity']);

        return array_slice($rankedTraits, 0, $n, true);
    }

    public static function getExistingProlificParticipant($prolificId)
    {
        if ($prolificId == null) {
            return null;
        }
        return static::where('prolific_id', $prolificId)->first();
    }

    private static function getPrimingGuidelines()
    {
        $path = storage_path('personalization_priming_guidelines.json');
        if (!is_file($path)) return [];
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }
}
