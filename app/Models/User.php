<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'family_name',
        'middle_name',
        'given_name',
        'gender_id',
        'passport_type_id',
        'passport_number',
        'birthday',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function passportType()
    {
        return $this->belongsTo(PassportType::class);
    }

    public function emails()
    {
        return $this->hasMany(UserHasEmail::class);
    }

    public function mobiles()
    {
        return $this->hasMany(UserHasMobile::class);
    }

    public function checkPassword($password)
    {
        return Hash::check($password, $this->password);
    }
}
