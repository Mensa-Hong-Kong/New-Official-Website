<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserLoginLog extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'login_ip',
        'status',
    ];
}
