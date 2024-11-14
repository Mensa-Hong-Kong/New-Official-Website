<?php

namespace App\Models;

use App\Concern\Models\Verifiable;
use App\Notifications\VerifyMobile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class UserHasMobile extends Model
{
    use HasFactory, Notifiable, Verifiable;

    protected $fillable = [
        'user_id',
        'mobile',
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sendVerifyWhatsapp()
    {
        $code = Str::random(6);
        $this->verifications()->create(['code' => $code]);
        $this->notify(new VerifyMobile($code));
    }

    public function routeNotificationForWhatsApp()
    {
        return $this->mobile;
    }
}
