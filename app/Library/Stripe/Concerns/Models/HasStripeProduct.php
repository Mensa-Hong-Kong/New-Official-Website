<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Client;
use App\Library\Stripe\Exceptions\AlreadyCreated;
use App\Library\Stripe\Exceptions\NotYetCreated;

trait HasStripeProduct
{
    use UpdatableBase;

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
