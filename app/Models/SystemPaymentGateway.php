<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemPaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
    
    public function admissionTestOrders()
    {
        return $this->morphMany(AdmissionTestOrder::class, 'gateway');
    }

    protected function type(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return 'Manual Handling';
            }
        );
    }
}
