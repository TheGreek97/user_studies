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
        "conclusion",
        "completed",
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
