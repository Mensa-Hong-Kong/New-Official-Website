<?php

namespace App\Models;

use App\Jobs\Stripe\Prices\SyncAdmissionTest as SyncPrice;
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
        static::created(
            function (AdmissionTestPrice $product) {
                SyncPrice::dispatch($product->id);
            }
        );
        static::updating(
            function (AdmissionTestPrice $price) {
                if ($price->isDirty('name')) {
                    $price->synced_to_stripe = false;
                    SyncPrice::dispatch($price->id);
                }
            }
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(AdmissionTestProduct::class, 'product_id');
    }
}
