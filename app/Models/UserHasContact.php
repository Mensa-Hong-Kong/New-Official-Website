<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact',
        'is_default',
    ];
}
