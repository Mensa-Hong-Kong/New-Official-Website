<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function verifications(): HasMany
    {
        return $this->hasMany(ContactHasVerification::class, 'contact_id');
    }

    public function lastVerification(): HasOne
    {
        return $this->hasOne(ContactHasVerification::class, 'contact_id')
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
