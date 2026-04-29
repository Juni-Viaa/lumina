<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangePassowrd extends Model
{
    protected $fillable = [
        'user_id',
        'current_password',
        'new_password',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
