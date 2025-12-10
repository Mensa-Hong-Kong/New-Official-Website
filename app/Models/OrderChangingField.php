<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderChangingField extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_change_id',
        'key',
        'origin_value',
        'new_value',
    ];

    public function orderChange()
    {
        return $this->belongsTo(OrderChangingLog::class);
    }
}
