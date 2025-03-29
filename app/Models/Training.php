<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $table = "trainings";

    protected $fillable = [
        "introduction",
        "scenario",
        "defense_strategies",
        "exercises",
        "conclusions",
        "completed",
        'user_id',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setToNonPersonalizedVersion()
    {
        $user_name = $this->user->name;
        $intro = "";
        $intro = str_replace("{USER_NAME}", $user_name, $intro);
        $this->introduction = $intro;

        $scenario = "";
        $scenario = str_replace("{USER_NAME}", $user_name, $scenario);
        $this->scenario = $scenario;

        $defense_strategies = "";
        $defense_strategies = str_replace("{USER_NAME}", $user_name, $defense_strategies);
        $this->defense_strategies = $defense_strategies;

        $exercises = "";
        $exercises = str_replace("{USER_NAME}", $user_name, $exercises);
        $this->exercises = $exercises;

        $conclusions = "";
        $conclusions = str_replace("{USER_NAME}", $user_name, $conclusions);
        $this->conclusions = $conclusions;
        $this->save();
    }
}
