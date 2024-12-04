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
        $code = Str::random(6);
        $this->verifications()->create([
            'verify_code' => $code,
            'closed_at' => now()->addMinutes(5),
        ]);
        $this->notify(new VerifyEmail($code));
    }

    protected static function boot(): void
    {
        static::created(function (UserHasEmail $model) {
            $model->sendVerifyEmail();
        });
    }

    public function routeNotificationForMail(Notification $notification): array
    {
        return [$this->email => $this->user->given_name];
    }
}
