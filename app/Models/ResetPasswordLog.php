<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResetPasswordLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'passport_type_id',
        'passport_number',
        'user_id',
        'contact_type',
        'creator_id',
        'creator_ip',
    ];

    public function passportType()
    {
        return $this->belongsTo(PassportType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
