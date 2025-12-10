<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_change_id',
        'amount',
    ];

    public function orderChange()
    {
        return $this->belongsTo(OrderChangingLog::class);
    }
}
