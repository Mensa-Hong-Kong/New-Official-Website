<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $product_name
 * @property string|null $price_name
 * @property numeric $price
 * @property int|null $minimum_age
 * @property int|null $maximum_age
 * @property int $quota
 * @property string $status
 * @property \Illuminate\Support\Carbon $expired_at
 * @property string $gateway_type
 * @property int $gateway_id
 * @property string|null $reference_number
 * @property numeric|null $gateway_payment_fee
 * @property int $returned_quota
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $gateway
 * @property-read mixed $has_unused_quota
 * @property-read \App\Models\AdmissionTest|null $lastTest
 * @property-read mixed $quota_expired_on
 * @property-read \App\Models\AdmissionTestHasCandidate|null $pivot
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
        'status',
        'expired_at',
        'gateway_type',
        'gateway_id',
        'reference_number',
        'gateway_payment_fee',
        'returned_quota',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gateway()
    {
        return $this->morphTo();
    }

    public function tests()
    {
        return $this->belongsToMany(AdmissionTest::class, AdmissionTestHasCandidate::class, 'order_id', 'test_id');
    }

    public function attendedTests()
    {
        return $this->tests()->where('is_present', true);
    }

    public function lastTest()
    {
        return $this->hasOneThrough(AdmissionTest::class, AdmissionTestHasCandidate::class, 'order_id', 'id', 'id', 'test_id')
            ->latest('testing_at');
    }

    public function lastAttendedTest()
    {
        return $this->lastTest()
            ->where('is_present', true);
    }

    public function quotaExpiredOn(): Attribute
    {
        $order = $this;

        return Attribute::make(
            get: function () use ($order) {
                $quotaValidityMonths = config('app.admissionTestQuotaValidityMonths', 0);

                return $quotaValidityMonths ?
                    ($order->lastAttendedTest?->testing_at ?? $order->created_at)
                        ->addMonths(
                            $quotaValidityMonths +
                                $order->lastAttendedTest?->type->interval_month
                        ) : null;
            }
        );
    }

    public function hasUnusedQuota(): Attribute
    {
        $order = $this;

        return Attribute::make(
            get: function () use ($order) {
                return $order->status === 'succeeded' &&
                    $order->returned_quota + $order->attendedTests()->count() < $order->quota &&
                    (
                        ! $order->quotaExpiredOn ||
                        $order->quotaExpiredOn >= now()
                    );
            }
        );
    }
}
