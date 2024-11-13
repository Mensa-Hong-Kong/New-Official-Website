<?php

namespace App\Models;

use App\Concern\Models\Verifiable;
use App\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class UserHasEmail extends Model
{
    use HasFactory, Notifiable, Verifiable;

    protected $fillable = [
        'user_id',
        'email',
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sendVerifyEmail()
    {
        $uuid = Str::random(32);
        $this->verifications()->create(['verify_code' => $uuid]);
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
