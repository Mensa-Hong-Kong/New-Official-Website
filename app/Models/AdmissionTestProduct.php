<?php

namespace App\Models;

use App\Jobs\Stripe\Products\SyncAdmissionTest as SyncProduct;
use App\Library\Stripe\Concerns\Models\HasStripeProduct;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
