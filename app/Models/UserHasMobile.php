<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasMobile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mobile',
        'verified_at',
        'is_default',
    ];
}