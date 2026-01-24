<?php

namespace App\Models;

use App\Library\Stripe\Concerns\Models\HasStripeCheckout;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionTestOrder extends Model
{
    use HasFactory, HasStripeCheckout;

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

    public function customer()
    {
        return $this->user;
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
}
