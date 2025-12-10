<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderChangingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_type',
        'order_id',
        'type',
        'description',
    ];

    public function order()
    {
        return $this->morphTo();
    }

    public function fields()
    {
        return $this->hasMany(OrderChangingField::class);
    }

    public function statement()
    {
        return $this->hasOne(OrderChangingStatement::class);
    }

    public function refund()
    {
        return $this->hasOne(OrderRefund::class);
    }
}
