<?php

namespace App\Models;

use App\Notifications\VerifyContact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class UserHasContact extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'type',
        'contact',
        'is_default',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(
            function (UserHasContact $contact) {
                if ($contact->is_default && $contact->type == 'email') {
                    $contact->user()->update(['synced_to_stripe' => false]);
                }
            }
        );
        static::updated(
            function (UserHasContact $contact) {
                if ($contact->wasChanged('is_default')) {
                    if ($contact->type == 'email') {
                        $contact->user()->update(['synced_to_stripe' => false]);
                    }
                    if ($contact->is_default) {
                        UserHasContact::where('type', $contact->type)
                            ->where('user_id', $contact->user_id)
                            ->whereNot('id', $contact->id)
                            ->update(['is_default' => false]);
                        $contacts = UserHasContact::where('type', $contact->type)
                            ->where('contact', $contact->contact)
                            ->whereNot('id', $contact->id)
                            ->get(['id', 'user_id']);
                        if (count($contacts)) {
                            if ($contact->type == 'email') {
                                User::whereIn('id', $contacts->pluck('user_id')->toArray())
                                    ->update(['synced_to_stripe' => false]);
                            }
                            UserHasContact::whereIn('id', $contacts->pluck('id')->toArray())
                                ->update(['is_default' => false]);
                        }
                    }
                }
            }
        );
    }

    public function getIsDefaultAttribute($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(ContactHasVerification::class, 'contact_id');
    }

    public function lastVerification(): HasOne
    {
        return $this->hasOne(ContactHasVerification::class, 'contact_id')
            ->latest('id');
    }

    public function routeNotificationForMail(): array
    {
        return [$this->contact => $this->user->given_name];
    }

    public function routeNotificationForWhatsApp()
    {
        return $this->contact;
    }

    public function newVerifyCode()
    {
        $code = App::environment('testing') ? '123456' : Str::random(6);
        ContactHasVerification::create([
            'contact_id' => $this->id,
            'contact' => $this->contact,
            'type' => $this->type,
            'code' => $code,
            'closed_at' => now()->addMinutes(5),
            'creator_id' => $this->user_id,
            'creator_ip' => request()->ip(),
        ]);

        return $code;
    }

    public function sendVerifyCode()
    {
        $this->notify(new VerifyContact($this->type, $this->newVerifyCode()));
    }

    public function isVerified(): bool
    {
        return $this->lastVerification && $this->lastVerification->verified_at && ! $this->lastVerification->expired_at;
    }

    public function isRequestTooFast()
    {
        return $this->lastVerification && $this->lastVerification->created_at > now()->subMinute();
    }

    public function isRequestTooManyTime(): bool
    {
        return ContactHasVerification::where('type', $this->type)
            ->where('contact', $this->contact)
            ->where('created_at', '>=', now()->subDay())
            ->where('middleware_should_count', true)
            ->count() >= 5;
    }
}
