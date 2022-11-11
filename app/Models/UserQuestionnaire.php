<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEmailQuestionnaire extends Model
{
    use HasFactory;
    protected $table = "useremailquestionnaire";

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function email()
    {
        return $this->belongsTo(Email::class);
    }
}
