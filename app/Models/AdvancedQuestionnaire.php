<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvancedQuestionnaire extends Model
{
    protected $table = "questionnaire_advanced_user";
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
