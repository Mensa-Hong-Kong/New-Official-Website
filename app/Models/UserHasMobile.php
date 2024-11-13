<?php

namespace App\Models;

use App\Notifications\VerifyMobile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class UserHasMobile extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'mobile',
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

    public function sendVerifyWhatsapp()
    {
        $uuid = Str::random(32);
        $this->verifyCode()->create(['verify_code' => $uuid]);
        $this->notify(new VerifyMobile($this->id, $uuid));
    }

    protected static function booted(): void
    {
        static::created(
            function () {
                $this->sendVerifyWhatsapp();
            })
        ;
    }

    public function routeNotificationForWhatsApp()
    {
        return $this->mobile;
    }
}
