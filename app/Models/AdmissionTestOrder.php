<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionTestOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'price_id',
        'quota',
        'type',
        'status',
    ];

    protected function status(): Attribute
    {

        return Attribute::make(
            get: function(mixed $value, array $attributes) {
                if(
                    $attributes['type'] == 'stripe' &&
                    $attributes['created_at']->addMinutes(config('services.stripe.lifetime')) >= now()
                ) {
                    return 'pending';
                }

                return $attributes['status'];
            }
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tests()
    {
        return $this->belongsToMany(AdmissionTest::class, AdmissionTestHasCandidate::class, 'order_id', 'test_id');
    }
}
