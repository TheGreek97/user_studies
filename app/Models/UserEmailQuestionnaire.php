<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEmailQuestionnaire extends Model
{
    use HasFactory;
    protected $table = "useremailquestionnaire";

    protected $fillable = [
        'email_id',
        'confidence',
        'phishing',
        'user_id',
        'title_email',
        'response_time_seconds',
        'phase',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function email()
    {
        return $this->belongsTo(Email::class);
    }
}
