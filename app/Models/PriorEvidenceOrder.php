<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriorEvidenceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'price_name',
        'price',
        'status',
        'expired_at',
        'gateway_type',
        'gateway_id',
        'reference_number',
        'gateway_payment_fee',
        'is_refunded',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gateway()
    {
        return $this->morphTo();
    }

    public function result()
    {
        return $this->hasOne(PriorEvidenceResult::class, 'order_id');
    }
}
