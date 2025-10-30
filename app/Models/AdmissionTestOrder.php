<?php

namespace App\Models;

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
}
