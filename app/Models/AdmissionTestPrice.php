<?php

namespace App\Models;

use App\Jobs\Stripe\Prices\SyncAdmissionTest as SyncPrice;
use App\Library\Stripe\Concerns\Models\HasStripePrice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property string|null $name
 * @property numeric $value
 * @property string|null $start_at
 * @property string|null $stripe_one_time_type_id
 * @property int $synced_one_time_type_to_stripe
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AdmissionTestProduct|null $product
 * @method static \Database\Factories\AdmissionTestPriceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereStripeOneTimeTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereSyncedOneTimeTypeToStripe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestPrice whereValue($value)
 * @mixin \Eloquent
 */
class AdmissionTestPrice extends Model
{
    use HasFactory, HasStripePrice;

    protected $fillable = [
        'product_id',
        'name',
        'value',
        'start_at',
        'stripe_one_time_type_id',
        'synced_one_time_type_to_stripe',
    ];

    protected $casts = [
        'synced_to_stripe' => 'boolean',
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
                    $price->synced_one_time_type_to_stripe = false;
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
