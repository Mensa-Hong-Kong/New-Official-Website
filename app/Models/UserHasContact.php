<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Notifications\Notifiable;

class UserHasContact extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'type',
        'contact',
        'is_default',
    ];

    public function verifications(): MorphMany
    {
        return $this->morphMany(Verification::class, 'verifiable');
    }

    public function lastVerification(): MorphOne
    {
        return $this->morphOne(Verification::class, 'verifiable')
            ->latest();
    }

    public function routeNotificationForMail(): array
    {
        return [$this->email => $this->user->given_name];
    }

    public function routeNotificationForWhatsApp()
    {
        return $this->mobile;
    }
}
