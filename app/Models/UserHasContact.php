<?php

namespace App\Models;

use App\Notifications\VerifyContact;
use App\Notifications\VerifyContactByQueuea;
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
            'code' => $code,
            'closed_at' => now()->addMinutes(5),
            'user_id' => $this->user_id,
            'user_ip' => request()->ip(),
        ]);

        return $code;
    }

    public function sendVerifyCode($shouldQueuea = false)
    {
        $class = VerifyContact::class;
        if ($shouldQueuea) {
            $class = VerifyContactByQueuea::class;
        }
        $this->notify(new $class($this->type, $this->newVerifyCode()));
    }

    protected static function booted(): void
    {
        static::created(
            function (UserHasContact $contact) {
                $type = ucfirst($contact->type);
                if (! $contact->user->{"default$type"}) {
                    $contact->sendVerifyCode(true);
                }
            }
        );
        static::updating(
            function (UserHasContact $contact) {
                $type = ucfirst($contact->tpye);
                if (! $contact->user->{"default$type"}) {
                    $contact->update(['is_default' => true]);
                    $contactIDs = ContactHasVerification::whereHas(
                        'contact', function ($query) use ($contact) {
                            $query->where('tpye', $contact->type)
                                ->where('contact', $contact->contact)
                                ->where('user_id', '!=', $contact->user_id);
                        }
                    )->whereNull('expired_at')
                        ->get('contact_id')
                        ->pluck('contact_id')
                        ->toArray();
                    if (count($contactIDs)) {
                        UserHasContact::whereIn('id', $contactIDs)
                            ->update(['is_default' => false]);
                        ContactHasVerification::whereIn('contact_id', $contactIDs)
                            ->update(['expired_at' => now()]);
                    }
                }
            }
        );
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
        $contact = $this;

        return ContactHasVerification::where('type', $this->type)
            ->where('created_at', '>=', now()->subDay())
            ->where(function ($query) use ($contact) {
                $query->where('user_id', $contact->user_id)
                    ->orWhere('user_ip', request()->ip());
            })->count() >= 5;
    }
}
