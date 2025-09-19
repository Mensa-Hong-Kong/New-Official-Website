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
        'gatewayable_type',
        'gatewayable_id',
        'reference_number',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function gatewayable()
    {
        return $this->morphTo();
    }

    public function tests()
    {
        return $this->belongsToMany(AdmissionTest::class, AdmissionTestHasCandidate::class, 'order_id', 'test_id');
    }
}
