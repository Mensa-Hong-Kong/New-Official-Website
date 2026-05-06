<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function futureTest()
    {
        return $this->lastTest()
            ->whereNull('is_present');
    }

    public function hasUnusedQuota(): Attribute
    {
        $order = $this;

        return Attribute::make(
            get: function () use ($order) {
                $quotaValidityMonths = config('app.admissionTestQuotaValidityMonths', 0);

                return $order->status === 'succeeded' &&
                    $order->returned_quota + $order->attendedTests()->count() < $order->quota &&
                    (
                        ! $quotaValidityMonths ||
                        ($order->lastAttendedTest?->testing_at ?? $order->created_at)
                            ->addMonths(
                                $quotaValidityMonths +
                                    $order->lastAttendedTest?->type->interval_month
                            ) >= now()
                    );
            }
        );
    }
}
