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
        if (! $this->stripe) {
            if ($this->stripe_id) {
                $this->stripe = Client::products()->find($this->stripe_id);
            } else {
                $this->stripe = Client::products()->first([
                    'metadata' => [
                        'type' => __CLASS__,
                        'id' => $this->id,
                    ],
                ]);
                if ($this->stripe) {
                    $this->update([
                        'stripe_id' => $this->stripe['id'],
                        'synced_to_stripe' => $this->name == $this->stripe['name'],
                    ]);
                }
            }
        }

        return $this->stripe;
    }

    public function stripeCreate(): array
    {
        if ($this->stripe_id) {
            throw new AlreadyCreated($this, 'product');
        }
        $this->getStripe();
        if (! $this->stripe) {
            $this->stripe = Client::products()->create([
                'name' => $this->name,
                'metadata' => [
                    'type' => __CLASS__,
                    'id' => $this->id,
                ],
            ]);
            $this->update([
                'stripe_id' => $this->stripe['id'],
                'synced_to_stripe' => $this->name == $this->stripe['name'],
            ]);
        }

        return $this->stripe;
    }

    public function stripeUpdate(): array
    {
        if (! $this->stripe_id) {
            throw new NotYetCreated($this, 'product');
        }
        $this->stripe = Client::products()->update(
            $this->stripe_id,
            ['name' => $this->name]
        );
        $this->update(['synced_to_stripe' => $this->name == $this->stripe['name']]);

        return $this->stripe;
    }
}
