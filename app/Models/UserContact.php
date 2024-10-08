<?php

namespace App\Models;

use App\Notifications\VerifyContact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class UserContact extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'type_id',
        'contact',
        'is_display',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type(){
        return $this->belongsTo(ContactType::class, 'type_id');
    }

    public function validates(): MorphMany
    {
        return $this->morphMany(Validate::class, 'validatable');
    }

    public function validated(): MorphOne
    {
        return $this->morphOne(Validate::class, 'validatable')
            ->where('status', true)
            ->where(
                function($query) {
                    $query->whereNull('expiry_at')
                        ->orWhere('expiry_at', '<=', now());
                }
            );
    }

    public function lastValidate(): MorphOne
    {
        return $this->morphOne(Validate::class, 'validatable')
            ->latest('id');
    }

    public function routeNotificationForMail(Notification $notification)
    {
        return [$this->contact => $this->user->given_name];
    }

    public function routeNotificationForWhatsApp()
    {
        return $this->contact;
    }

    public function sendVerificationCode()
    {
        $code = Str::random(6);
        $this->validates()->create([
            'code' => $code,
            'expiry_at' => now()->addMinutes(5),
        ]);
        $this->notify(new VerifyContact($this, $code));
    }

    protected static function booted(): void
    {
        static::created(
            function (UserContact $contact) {
                if($contact->type->can_verify) {
                    $contact->sendVerificationCode();
                }
            })
        ;
    }
}
