<?php

namespace App\Models;

use App\Jobs\Stripe\Customers\CreateUser;
use App\Library\Stripe\Concerns\Models\HasStripeCustomer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
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
        'synced_to_stripe',
        'address_id',
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
        'birthday' => 'date',
        'synced_to_stripe' => 'boolean',
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

    public function countAge(Carbon $time): float|int
    {
        return $this->birthday->diffInMonths($time->startOfDay()) / 12;
    }

    protected function age(): Attribute
    {
        $user = $this;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($user) {
                return $user->countAge(now());
            }
        );
    }

    public function countAgeForPsychology(Carbon $time): float|int
    {
        $diffMonths = floor($this->birthday->diffInMonths($time->startOfDay()));
        $diffDays = $time->format('d') - $this->birthday->format('d');
        if ($diffDays < 0) {
            $diffDays = $diffDays + 30;
            if ($diffDays != 0) {
                $diffMonths = $diffMonths - 1;
            }
        }

        return ($diffMonths + $diffDays / 30) / 12;
    }

    protected function ageForPsychology(): Attribute
    {
        $user = $this;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($user) {
                return $user->countAgeForPsychology(now());
            }
        );
    }

    public function hasOtherSamePassportUserJoinedFutureTest(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return User::whereNot('id', $this->id)
                    ->where('passport_type_id', $attributes['passport_type_id'])
                    ->where('passport_number', $attributes['passport_number'])
                    ->whereHas(
                        'admissionTests', function ($query) {
                            $query->where('testing_at', '>', now());
                        }
                    )->exists();
            }
        );
    }

    public function lastAttendedAdmissionTestOfOtherSamePassportUser(): Attribute
    {
        $table = $this->getTable();

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($table) {
                return AdmissionTest::whereHas(
                    'candidates', function ($query) use ($attributes, $table) {
                        $query->where('passport_number', $attributes['passport_number'])
                            ->where('passport_type_id', $attributes['passport_type_id'])
                            ->whereNot("$table.id", $attributes['id'])
                            ->where('is_present', true);
                    }
                )->orderByDesc('testing_at')
                    ->first();
            }
        );
    }

    public function hasOtherSamePassportUserAttendedAdmissionTest(): Attribute
    {
        $user = $this;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($user) {
                return (bool) $user->lastAttendedAdmissionTestOfOtherSamePassportUser;
            }
        );
    }

    public function passedAdmissionTest()
    {
        return $this->hasOneThrough(AdmissionTest::class, AdmissionTestHasCandidate::class, 'user_id', 'id', 'id', 'test_id')
            ->where('is_pass', true);
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

    public function lastLoginLog(): HasOne
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

    public function proctorTests()
    {
        return $this->belongsToMany(AdmissionTest::class, AdmissionTestHasProctor::class, 'user_id', 'test_id');
    }

    public function admissionTests()
    {
        return $this->belongsToMany(AdmissionTest::class, AdmissionTestHasCandidate::class, 'user_id', 'test_id')
            ->withPivot(['order_id', 'seat_number', 'is_present', 'is_pass']);
    }

    public function futureAdmissionTest()
    {
        return $this->hasOneThrough(AdmissionTest::class, AdmissionTestHasCandidate::class, 'user_id', 'id', 'id', 'test_id')
            ->where('testing_at', '>', now());
    }

    public function lastAdmissionTest()
    {
        return $this->hasOneThrough(AdmissionTest::class, AdmissionTestHasCandidate::class, 'user_id', 'id', 'id', 'test_id')
            ->where('expect_end_at', '>', now()->subHour())
            ->latest('testing_at');
    }

    public function lastAttendedAdmissionTest()
    {
        return $this->hasOneThrough(AdmissionTest::class, AdmissionTestHasCandidate::class, 'user_id', 'id', 'id', 'test_id')
            ->where('is_present', true)
            ->latest('testing_at');
    }

    public function admissionTestOrders()
    {
        return $this->hasMany(AdmissionTestOrder::class);
    }

    public function lastAdmissionTestOrder()
    {
        return $this->hasOne(AdmissionTestOrder::class)
            ->latest('id');
    }

    public function hasUnusedQuotaAdmissionTestOrder()
    {
        $orderTable = (new AdmissionTestOrder)->getTable();
        $return = $this->hasOne(AdmissionTestOrder::class)
            ->where('status', 'succeeded')
            ->whereHas(
                'attendedTests', null, '<',
                DB::raw("$orderTable.quota - $orderTable.returned_quota")
            );
        $quotaValidityMonths = config('app.admissionTestQuotaValidityMonths');
        if ($quotaValidityMonths) {
            $return->leftJoinRelation('attendedTests as attendedTests.type as type')
                ->where(
                    DB::raw("
                        if(
                            attendedTests.testing_at IS NOT NULL,
                            DATE_ADD(
                                attendedTests.testing_at,
                                INTERVAL type.interval_month + $quotaValidityMonths MONTH
                            ),
                            DATE_ADD(
                                $orderTable.created_at,
                                INTERVAL $quotaValidityMonths MONTH
                            )
                        )
                    "), '>=', now()
                )->select(["$orderTable.*"]);
        }

        return $return;
    }

    public function memberTransfers()
    {
        return $this->hasMany(MembershipTransfer::class);
    }

    public function priorEvidenceOrders()
    {
        return $this->hasMany(PriorEvidenceOrder::class);
    }

    public function acceptedPriorEvidence()
    {
        return $this->hasOneThrough(PriorEvidenceResult::class, PriorEvidenceOrder::class, 'user_id', 'order_id', 'id', 'id')
            ->where('is_accepted', true);
    }

    public function membershipOrders()
    {
        return $this->hasMany(MembershipOrder::class);
    }

    public function hasQualificationOfMembership(): Attribute
    {
        $user = $this;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($user) {
                return $user->member || $user->passedAdmissionTest ||
                    $user->acceptedPriorEvidence || $user->memberTransfers()
                        ->where('is_accepted', true)
                        ->exists();
            }
        );
    }

    public function hasSamePassportAlreadyQualificationOfMembership(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return User::where('passport_type_id', $attributes['passport_type_id'])
                    ->where('passport_number', $attributes['passport_number'])
                    ->where(
                        function ($query) {
                            $query->has('member')
                                ->orHas('passedAdmissionTest')
                                ->orHas('acceptedPriorEvidence')
                                ->orWhereHas(
                                    'memberTransfers', function ($query) {
                                        $query->where('is_accepted', true);
                                    }
                                );
                        }
                    )->exists();
            }
        );
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function canEditPassportInformation(): Attribute
    {
        $user = $this;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($user) {
                return ! $user->lastAttendedAdmissionTest && // proctor will update on admission test
                    ( // avoid user update overwrite proctor updated data
                        ! $user->lastAdmissionTest ||
                        $user->lastAdmissionTest->testing_at > now()->addHours(2)
                    ) &&
                    ! $user->memberTransfers()
                        ->where('is_accepted', true)
                        ->orWhereNull('is_accepted')
                        ->exists() &&
                    ! $user->priorEvidenceOrders()
                        ->where('is_returned', false)
                        ->whereIn('status', ['succeeded', 'partial funded', 'full refunded'])
                        ->whereDoesntHave(
                            'result', function ($query) {
                                $query->whereNot('is_accepted', true)
                                    ->whereNotNull('is_accepted');
                            }
                        )->exists() &&
                    ! $user->member;
            }
        );
    }
}
