<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Client;
use App\Library\Stripe\Exceptions\AlreadyCreatedPrice;
use App\Library\Stripe\Exceptions\NotYetCreated;
use App\Library\Stripe\Exceptions\NotYetCreatedProduct;
use App\Library\Stripe\Jobs\SyncPriceToStripe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $value
 * @property-read Collection<int, HasStripeProduct> $product
 *
 * @method \Illuminate\Database\Eloquent\Model update($attributes = [], $options = [])
 *
 * @method static void created(\Closure|string $callback)
 * @method static void updating(\Closure|string $callback)
 */
trait HasStripePrice
{
    public array $stripeData = [
        'one_time' => null,
        'recurring' => null,
    ];

    public static function bootHasStripePrice()
    {
        static::created(
            function (Model $product): void {
                SyncPriceToStripe::dispatch(
                    __CLASS__,
                    $product->id
                );
            }
        );
        static::updating(
            function (Model $product): void {
                if ($product->isDirty('name')) {
                    if (in_array('stripe_one_time_type_id', $product->getFillable())) {
                        $product->synced_one_time_type_to_stripe = false;
                    }
                    if (in_array('stripe_recurring_type_id', $product->getFillable())) {
                        $product->synced_recurring_type_to_stripe = false;
                    }
                    SyncPriceToStripe::dispatch(
                        __CLASS__,
                        $product->id
                    );
                }
            }
        );
    }

    abstract public function product(): BelongsTo;

    private function validType(string $type): void
    {
        if (! in_array($type, ['one_time', 'recurring'])) {
            throw new \InvalidArgumentException('The type field does not exist in one_time and recurring.');
        }
    }

    public function getStripe(string $type): ?array
    {
        $this->validType($type);
        if (! $this->stripeData[$type]) {
            if ($this->{"stripe_{$type}_type_id"}) {
                $this->stripeData[$type] = Client::prices()->find($this->{"stripe_{$type}_type_id"});
            } else {
                $this->stripeData[$type] = Client::prices()->first([
                    'type' => $type,
                    'metadata' => [
                        'type' => __CLASS__,
                        'id' => $this->id,
                    ],
                ]);
                if ($this->stripeData[$type]) {
                    $this->update([
                        "stripe_{$type}_type_id" => $this->stripeData[$type]['id'],
                        "synced_{$type}_type_to_stripe" => $this->name == $this->stripeData[$type]['nickname'],
                    ]);
                }
            }
        }

        return $this->stripeData[$type];
    }

    public function stripeCreate(string $type): array
    {
        $this->validType($type);
        if ($this->{"stripe_{$type}_type_id"}) {
            throw new AlreadyCreatedPrice($this, $type);
        }
        $this->getStripe($type);
        if (! $this->stripeData[$type]) {
            if (! $this->product->stripe_id) {
                throw new NotYetCreatedProduct($this);
            }
            $data = [
                'product' => $this->product->stripe_id,
                'nickname' => $this->name,
                'type' => $type,
                'currency' => config('stripe.currency', 'hkd'),
                'unit_amount' => $this->value,
                'metadata' => [
                    'type' => __CLASS__,
                    'id' => $this->id,
                ],
            ];
            if ($type == 'recurring') {
                $data['recurring'] = [
                    'interval' => $this->product->interval,
                    'interval_count' => $this->product->intervalCount,
                ];
            }
            $this->stripeData[$type] = Client::prices()->create($data);
            $this->update([
                "stripe_{$type}_type_id" => $this->stripeData[$type]['id'],
                "synced_{$type}_type_to_stripe" => $this->name == $this->stripeData[$type]['nickname'],
            ]);
        }

        return $this->stripeData[$type];
    }

    public function stripeUpdate(string $type): array
    {
        $this->validType($type);
        if (! $this->{"stripe_{$type}_type_id"}) {
            throw new NotYetCreated($this, 'price');
        }
        $this->stripeData[$type] = Client::prices()->update(
            $this->{"stripe_{$type}_type_id"},
            ['nickname' => $this->name],
        );
        $this->update(["synced_{$type}_type_to_stripe" => $this->name == $this->stripeData[$type]['nickname']]);

        return $this->stripeData[$type];
    }

    public function stripeUpdateOrCreate(string $type): array
    {
        $this->validType($type);
        if (! $this->{"stripe_{$type}_type_id"}) {
            $this->stripeCreate($type);
        }
        if (! $this->{"synced_{$type}_type_to_stripe"}) {
            $this->stripeUpdate($type);
        }
        if (! $this->stripeData[$type]) {
            $this->getStripe($type);
        }

        return $this->stripeData[$type];
    }
}
