<?php

namespace App\Models;

use App\Jobs\Stripe\Customers\CreateUser;
use App\Library\Stripe\Concerns\Models\HasStripeCustomer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Kyslik\ColumnSortable\Sortable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, HasStripeCustomer, Notifiable, Sortable;

    protected $fillable = [
        'username',
        'password',
        'family_name',
        'middle_name',
        'given_name',
        'gender_id',
        'passport_type_id',
        'passport_number',
        'birthday',
        'stripe_id',
        'synced_to_stripe',
    ];

    public $sortable = [
        'birthday',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(
            function (User $user) {
                CreateUser::dispatch($user->id);
            }
        );
        static::updating(
            function (User $user) {
                if ($user->isDirty(['family_name', 'middle_name', 'given_name'])) {
                    $user->synced_to_stripe = false;
                }
            }
        );
    }

    protected function adornedName(): Attribute
    {
        $member = $this->member;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($member) {
                $name = [
                    '1' => $attributes['given_name'],
                    '4' => $attributes['family_name'],
                ];
                if ($attributes['middle_name'] != '') {
                    $name['2'] = $attributes['middle_name'];
                }
                if ($member) {
                    if ($member->prefix_name) {
                        $name['0'] = "$member->prefix_name.";
                    }
                    if ($member->nickname) {
                        $name['3'] = "'$member->nickname'";
                    }
                    if ($member->suffix_name) {
                        $name['5'] = "$member->suffix_name.";
                    }
                }
                ksort($name);

                return implode(' ', $name);
            }
        );
    }

    protected function preferredName(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $name = [
                    '1' => $attributes['given_name'],
                    '3' => $attributes['family_name'],
                ];
                if ($attributes['middle_name'] != '') {
                    $name['2'] = $attributes['middle_name'];
                }
                ksort($name);

                return implode(' ', $name);
            }
        );
    }

    protected function stripeName(): string
    {
        return $this->preferredName;
    }

    protected function stripeEmail(): ?string
    {
        return $this->defaultEmail;
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function passportType(): BelongsTo
    {
        return $this->belongsTo(PassportType::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(UserHasContact::class);
    }

    public function emails(): HasMany
    {
        return $this->contacts()
            ->where('type', 'email');
    }

    public function mobiles(): HasMany
    {
        return $this->contacts()
            ->where('type', 'mobile');
    }

    public function checkPassword($password): bool
    {
        return Hash::check($password, $this->password);
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(UserLoginLog::class);
    }

    public function lastLoginLogs(): HasOne
    {
        return $this->hasOne(UserLoginLog::class)
            ->latest('id');
    }

    public function defaultEmail(): HasOne
    {
        return $this->hasOne(UserHasContact::class)
            ->where('type', 'email')
            ->where('is_default', true)
            ->whereHas(
                'verifications', function ($query) {
                    $query->whereNull('expired_at')
                        ->whereNotNull('verified_at');
                }
            );
    }

    public function defaultMobile(): HasOne
    {
        return $this->hasOne(UserHasContact::class)
            ->where('type', 'mobile')
            ->where('is_default', true)
            ->whereHas(
                'verifications', function ($query) {
                    $query->whereNull('expired_at')
                        ->whereNotNull('verified_at');
                }
            );
    }

    public function routeNotificationForMail(): array
    {
        return [$this->defaultEmail->contact => $this->given_name];
    }

    public function routeNotificationForWhatsApp(): string|int
    {
        return $this->defaultMobile->contact;
    }

    public function contactVerifications(): HasMany
    {
        return $this->hasMany(ContactHasVerification::class, 'creator_id');
    }

    public function isRequestTooManyTimeVerifyCode($contactType): bool
    {
        return $this->contactVerifications()
            ->where('type', $contactType)
            ->where('created_at', '>=', now()->subDay())
            ->where('middleware_should_count', true)
            ->count() >= 5;
    }

    public function isAdmin()
    {
        return $this->getAllPermissions()->count() || $this->hasRole('Super Administrator');
    }

    public function member()
    {
        return $this->hasOne(Member::class);
    }

    public function isActiveMember()
    {
        return (bool) $this->member && $this->member->is_active;
    }

    public function proctorTests()
    {
        return $this->belongsToMany(AdmissionTest::class, AdmissionTestHasProctor::class, 'user_id', 'test_id');
    }

    public function admissionTests()
    {
        return $this->belongsToMany(AdmissionTest::class, AdmissionTestHasCandidate::class, 'user_id', 'test_id')
            ->withPivot(['is_present', 'is_pass']);
    }

    public function futureAdmissionTest()
    {
        return $this->hasOneThrough(AdmissionTest::class, AdmissionTestHasCandidate::class, 'user_id', 'id', 'id', 'test_id')
            ->where('testing_at', '>', now());
    }

    public function lastAdmissionTest()
    {
        return $this->hasOneThrough(AdmissionTest::class, AdmissionTestHasCandidate::class, 'user_id', 'id', 'id', 'test_id')
            ->where('testing_at', '<=', now())
            ->latest('testing_at');
    }

    public function hasPassedAdmissionTest()
    {
        return in_array(
            true,
            $this->admissionTests
                ->pluck('pivot.is_pass')
                ->toArray(),
        );
    }

    public function hasQualificationOfMembership()
    {
        return $this->member || $this->hasPassedAdmissionTest();
    }

    public function hasSamePassportAlreadyQualificationOfMembership()
    {
        return self::where('passport_type_id', $this->passport_type_id)
            ->where('passport_number', $this->passport_number)
            ->where(
                function ($query) {
                    $query->has('member')
                        ->orWhereHas(
                            'admissionTests', function ($query) {
                                $query->where('is_pass', true);
                            }
                        );
                }
            )->exists();
    }

    public function hasOtherSamePassportUserTested(?AdmissionTest $ignore = null)
    {
        return self::where('passport_type_id', $this->passport_type_id)
            ->where('passport_number', $this->passport_number)
            ->whereNot('id', $this->id)
            ->whereHas(
                'admissionTests', function ($query) use ($ignore) {
                    $query->where('is_present', true)
                        ->where('testing_at', '<', now());
                    if ($ignore) {
                        $query->whereNot('test_id', $ignore->id);
                    }
                }
            )->exists();
    }

    public function hasTestedWithinDateRange($form, $to, ?AdmissionTest $ignore = null)
    {
        foreach ($this->admissionTests as $test) {
            if (
                $test->testing_at >= $form && $test->testing_at <= $to &&
                (! $ignore || $ignore->id != $test->id)
            ) {
                return true;
            }
        }

        return false;
    }

    public function hasOtherUserSamePassportJoinedFutureTest()
    {
        return User::whereNot('id', $this->id)
            ->where('passport_type_id', $this->passport_type_id)
            ->where('passport_number', $this->passport_number)
            ->whereHas(
                'admissionTests', function ($query) {
                    $query->where('testing_at', '>', now());
                }
            )->exists();
    }
}
