<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'product_name',
        'price_name',
        'price',
        'status',
        'expired_at',
        'from_year',
        'to_year',
        'gateway_type',
        'gateway_id',
        'reference_number',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, Member::class, 'id', 'id', 'member_id', 'user_id');
    }

    public function gateway()
    {
        return $this->morphTo();
    }
}
