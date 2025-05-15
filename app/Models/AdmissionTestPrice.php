<?php

namespace App\Models;

use App\Library\Stripe\Concerns\Models\HasStripePrice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionTestPrice extends Model
{
    use HasFactory, HasStripePrice;

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(AdmissionTestProduct::class, 'product_id');
    }
}
