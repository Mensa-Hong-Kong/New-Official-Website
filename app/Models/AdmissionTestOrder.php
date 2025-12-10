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

    public function changingLogs()
    {
        return $this->morphMany(OrderChangingLog::class, 'order');
    }

    public function countRefundedAmount()
    {
        return $this->changingLogs()
            ->joinRelationship('refund as refund')
            ->where('type', 'refund')
            ->sum('refund.amount');
    }

    public function refundedAmount(): Attribute
    {
        $order = $this;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($order) {
                return $order->countRefundedAmount();
            }
        );
    }

    public function refundableAmount(): Attribute
    {
        $order = $this;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($order) {
                $this->price - $order->refundedAmount;
            }
        );
    }
}
