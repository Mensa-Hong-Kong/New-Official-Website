<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherPaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'display_order',
    ];

    public function admissionTestOrders()
    {
        return $this->morphMany(AdmissionTestOrder::class, 'gatewayable');
    }
}
