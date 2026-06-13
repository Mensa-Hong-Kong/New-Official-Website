<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Client;
use App\Library\Stripe\Exceptions\AlreadyCreated;
use App\Library\Stripe\Exceptions\NotYetCreated;
use App\Library\Stripe\Jobs\SyncProductToStripe;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasStripeCustomer
 *
 * @property int $id
 * @property string $name
 * @property string $synced_to_stripe
 *
 * @mixin Model
 *
 * @method static void created(\Closure|string $callback)
 * @method static void updating(\Closure|string $callback)
 */
trait HasStripeProduct
{
    use UpdatableBase;

    public static function bootHasStripeProduct()
    {
        static::created(
            function (Model $product): void {
                SyncProductToStripe::dispatch(
                    __CLASS__,
                    $product->id
                );
            }
        );
        static::updating(
            function (Model $product): void {
                if ($product->isDirty('name')) {
                    $product->synced_to_stripe = false;
                    SyncProductToStripe::dispatch(
                        __CLASS__,
                        $product->id
                    );
                }
            }
        );
    }

    public function getStripe(): ?array
    {
        if (! $this->stripeData) {
            if ($this->stripe_id) {
                $this->stripeData = Client::products()->find($this->stripe_id);
            } else {
                $this->stripeData = Client::products()->first([
                    'metadata' => [
                        'type' => __CLASS__,
                        'id' => $this->id,
                    ],
                ]);
                if ($this->stripeData) {
                    $this->update([
                        'stripe_id' => $this->stripeData['id'],
                        'synced_to_stripe' => $this->name == $this->stripeData['name'],
                    ]);
                }
            }
        }

        return $this->stripeData;
    }

    public function stripeCreate(): array
    {
        if ($this->stripe_id) {
            throw new AlreadyCreated($this, 'product');
        }
        $this->getStripe();
        if (! $this->stripeData) {
            $this->stripeData = Client::products()->create([
                'name' => $this->name,
                'metadata' => [
                    'type' => __CLASS__,
                    'id' => $this->id,
                ],
            ]);
            $this->update([
                'stripe_id' => $this->stripeData['id'],
                'synced_to_stripe' => $this->name == $this->stripeData['name'],
            ]);
        }

        return $this->stripeData;
    }

    public function stripeUpdate(): array
    {
        if (! $this->stripe_id) {
            throw new NotYetCreated($this, 'product');
        }
        $this->stripeData = Client::products()->update(
            $this->stripe_id,
            ['name' => $this->name]
        );
        $this->update(['synced_to_stripe' => $this->name == $this->stripeData['name']]);

        return $this->stripeData;
    }
}
