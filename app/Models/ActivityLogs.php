<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class ActivityLogs extends Model
{
    use HasFactory;
    protected $table = 'activity_logs';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mail()
    {
        return $this->belongsTo(Email::class);
    }
}
