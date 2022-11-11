<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];
    public $timestamps = false;

    public function logs()
    {
        return $this->hasMany(ActivityLogs::class);
    }
}
