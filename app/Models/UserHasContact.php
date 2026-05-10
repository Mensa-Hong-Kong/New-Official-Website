<?php

namespace App\Models;

use App\Notifications\VerifyContact;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $contact
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read bool $is_verified
 * @property-read \App\Models\ContactHasVerification|null $lastVerification
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ContactHasVerification> $verifications
 * @property-read int|null $verifications_count
 *
 * @method static \Database\Factories\UserHasContactFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasContact whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserHasContact extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'type',
        'contact',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
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

    protected function isVerified(): Attribute
    {
        $lastVerification = $this->lastVerification;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($lastVerification): bool {
                return $lastVerification &&
                    $lastVerification->verified_at &&
                    ! $lastVerification->expired_at;
            }
        );
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
