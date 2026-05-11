<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $product_name
 * @property string|null $price_name
 * @property numeric $price
 * @property int|null $minimum_age
 * @property int|null $maximum_age
 * @property int $quota
 * @property int|null $quota_validity_months
 * @property string $status
 * @property \Illuminate\Support\Carbon $expired_at
 * @property string $gateway_type
 * @property int $gateway_id
 * @property string|null $reference_number
 * @property numeric|null $gateway_payment_fee
 * @property int $returned_quota
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AdmissionTestHasCandidate|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTest> $attendedTests
 * @property-read int|null $attended_tests_count
 * @property-read Model|\Eloquent $gateway
 * @property-read bool $has_unused_quota
 * @property-read \App\Models\AdmissionTest|null $lastAttendedTest
 * @property-read \App\Models\AdmissionTest|null $lastTest
 * @property-read \Carbon\Carbon|null $quota_expired_on
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTest> $tests
 * @property-read int|null $tests_count
 * @property-read \App\Models\User|null $user
 *
 * @method static \Database\Factories\AdmissionTestOrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereGatewayPaymentFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereGatewayType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereMaximumAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereMinimumAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder wherePriceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereQuota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereQuotaValidityMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereReturnedQuota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestOrder whereUserId($value)
 *
 * @mixin \Eloquent
 */
class AdmissionTestOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'price_name',
        'price',
        'minimum_age',
        'maximum_age',
        'quota',
        'quota_validity_months',
        'status',
        'expired_at',
        'gateway_type',
        'gateway_id',
        'reference_number',
        'gateway_payment_fee',
        'returned_quota',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'expired_at' => 'datetime',
        'gateway_payment_fee' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gateway(): MorphTo
    {
        return $this->morphTo();
    }

    public function tests(): BelongsToMany
    {
        return $this->belongsToMany(AdmissionTest::class, AdmissionTestHasCandidate::class, 'order_id', 'test_id');
    }

    public function attendedTests(): BelongsToMany
    {
        return $this->tests()->where('is_present', true);
    }

    public function lastTest(): HasOneThrough
    {
        return $this->hasOneThrough(AdmissionTest::class, AdmissionTestHasCandidate::class, 'order_id', 'id', 'id', 'test_id')
            ->latest('testing_at');
    }

    public function lastAttendedTest(): HasOneThrough
    {
        return $this->lastTest()
            ->where('is_present', true);
    }

    public function quotaExpiredOn(): Attribute
    {
        $order = $this;

        return Attribute::make(
            get: function () use ($order): ?Carbon {
                if ($order->quota_validity_months) {
                    $date = (clone ($order->lastAttendedTest->testing_at ?? $order->created_at))
                        ->addMonths(
                            $order->quota_validity_months +
                                ($order->lastAttendedTest->type->interval_month ?? 0)
                        )->endOfDay();
                    $extendConfig = config('app.extendAdmissionTestQuotaExpiredDate');
                    if (
                        $extendConfig['whenAfterThan'] &&
                        $extendConfig['to']
                    ) {
                        $to = new Carbon($extendConfig['to'])->endOfDay();
                        if ($date->between(new Carbon($extendConfig['whenAfterThan'])->startOfDay(), $to)) {
                            return $to;
                        }
                    }

                    return $date;
                }

                return null;
            }
        );
    }

    public function hasUnusedQuota(): Attribute
    {
        $order = $this;

        return Attribute::make(
            get: function () use ($order): bool {
                return in_array($order->status, ['succeeded', 'partial refunded', 'full refunded']) &&
                    $order->returned_quota + $order->attendedTests()->count() < $order->quota &&
                    (
                        ! $order->quotaExpiredOn ||
                        $order->quotaExpiredOn >= now()
                    );
            }
        );
    }
}
