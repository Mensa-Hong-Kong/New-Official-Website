<?php

namespace App\Models;

use App\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class UserHasEmail extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'email',
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifyCode(): MorphOne
    {
        return $this->morphOne(VerifyCode::class, 'contact', 'type', 'type_id', 'id');
    }

    public function sendVerifyEmail()
    {
        $uuid = Str::random(32);
        $this->verifyCode()->create(['verify_code' => $uuid]);
        $this->notify(new VerifyEmail($this->id, $uuid));
    }

    protected static function booted(): void
    {
        static::created(
            function () {
                $this->sendVerifyEmail();
            })
        ;
    }

    public function routeNotificationForMail(Notification $notification): array
    {
        return [$this->email => $this->user->given_name];
    }
}
