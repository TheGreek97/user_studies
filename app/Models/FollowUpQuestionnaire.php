<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUpQuestionnaire extends Model
{
    use HasFactory;
    protected $table = "skillsquestionnaire";

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
