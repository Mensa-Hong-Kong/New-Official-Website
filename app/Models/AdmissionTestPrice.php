<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionTestPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'start_at',
        'stripe_id',
        'synced_to_stripe',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::updating(
            function (AdmissionTestPrice $price) {
                if ($price->isDirty('name')) {
                    $price->synced_to_stripe = false;
                }
            }
        );
    }

    public function product()
    {
        return $this->belongsTo(AdmissionTestProduct::class, 'product_id');
    }
}
