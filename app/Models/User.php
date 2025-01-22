<?php

namespace App\Models;

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
    use HasApiTokens, HasFactory, HasRoles, Notifiable, Sortable;

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

    protected function name(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $name = [
                    '1' => $attributes['given_name'],
                    '3' => $attributes['family_name'],
                ];
                if (! is_null($attributes['middle_name'])) {
                    $name['2'] = $attributes['middle_name'];
                }
                ksort($name);

                return implode(', ', $name);
            }
        );
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

    public function isMember()
    {
        return (bool) $this->member;
    }
}
