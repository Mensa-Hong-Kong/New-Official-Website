<?php

namespace App\Models;

use App\Jobs\Stripe\Products\SyncAdmissionTest as SyncProduct;
use App\Library\Stripe\Concerns\Models\HasStripeProduct;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $option_name
 * @property int|null $minimum_age
 * @property int|null $maximum_age
 * @property string|null $start_at
 * @property string|null $end_at
 * @property int $quota
 * @property string|null $stripe_id
 * @property bool $synced_to_stripe
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AdmissionTestPrice|null $price
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTestPrice> $prices
 * @property-read int|null $prices_count
 *
 * @method static \Database\Factories\AdmissionTestProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|AdmissionTestProduct newModelQuery()
 * @method static Builder<static>|AdmissionTestProduct newQuery()
 * @method static Builder<static>|AdmissionTestProduct query()
 * @method static Builder<static>|AdmissionTestProduct whereCreatedAt($value)
 * @method static Builder<static>|AdmissionTestProduct whereEndAt($value)
 * @method static Builder<static>|AdmissionTestProduct whereId($value)
 * @method static Builder<static>|AdmissionTestProduct whereInAgeRange(int|float $age)
 * @method static Builder<static>|AdmissionTestProduct whereInDateRange(\Carbon\Carbon $date)
 * @method static Builder<static>|AdmissionTestProduct whereMaximumAge($value)
 * @method static Builder<static>|AdmissionTestProduct whereMinimumAge($value)
 * @method static Builder<static>|AdmissionTestProduct whereName($value)
 * @method static Builder<static>|AdmissionTestProduct whereOptionName($value)
 * @method static Builder<static>|AdmissionTestProduct whereQuota($value)
 * @method static Builder<static>|AdmissionTestProduct whereStartAt($value)
 * @method static Builder<static>|AdmissionTestProduct whereStripeId($value)
 * @method static Builder<static>|AdmissionTestProduct whereSyncedToStripe($value)
 * @method static Builder<static>|AdmissionTestProduct whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class AdmissionTestProduct extends Model
{
    use HasFactory, HasStripeProduct;

    protected $fillable = [
        'name',
        'option_name',
        'minimum_age',
        'maximum_age',
        'start_at',
        'end_at',
        'quota',
        'stripe_id',
        'synced_to_stripe',
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
            function (AdmissionTestProduct $product) {
                SyncProduct::dispatch($product->id);
            }
        );
        static::updating(
            function (AdmissionTestProduct $product) {
                if ($product->isDirty('name')) {
                    $product->synced_to_stripe = false;
                    SyncProduct::dispatch($product->id);
                }
            }
        );
    }

    public function prices()
    {
        return $this->hasMany(AdmissionTestPrice::class, 'product_id');
    }

    public function price()
    {
        return $this->hasOne(AdmissionTestPrice::class, 'product_id')
            ->where(
                function ($query) {
                    $query->whereNull('start_at')
                        ->orWhere('start_at', '<=', now());
                }
            )
            ->latest('start_at')
            ->latest('updated_at');
    }

    public function scopeWhereInDateRange(Builder $query, Carbon $date)
    {
        return $query->where(
            function ($query) use ($date) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', $date);
            }
        )->where(
            function ($query) use ($date) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', $date);
            }
        );
    }

    public function scopeWhereInAgeRange(Builder $query, int|float $age)
    {
        $age = floor($age);

        return $query->where(
            function ($query) use ($age) {
                $query->whereNull('minimum_age')
                    ->orWhere('minimum_age', '<=', $age);
            }
        )->where(
            function ($query) use ($age) {
                $query->whereNull('maximum_age')
                    ->orWhere('maximum_age', '>=', $age);
            }
        );
    }
}
