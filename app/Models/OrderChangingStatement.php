<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderChangingStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_changing_log_id',
        'administrative_charge',
        'amount',
        'gateway_type',
        'gateway_id',
        'reference_number',
    ];

    public function changingLog()
    {
        return $this->belongsTo(OrderChangingLog::class);
    }

    public function gateway()
    {
        return $this->morphTo();
    }
}
