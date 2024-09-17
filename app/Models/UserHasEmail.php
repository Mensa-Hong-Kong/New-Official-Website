<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'verified_at',
        'is_default',
    ];
}
