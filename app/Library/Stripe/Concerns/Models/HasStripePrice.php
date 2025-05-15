<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Client;
use App\Library\Stripe\Exceptions\AlreadyCreated;
use App\Library\Stripe\Exceptions\NotYetCreated;
use App\Library\Stripe\Exceptions\NotYetCreatedProduct;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasStripePrice
{
    use UpdatableBase;

    abstract public function product(): BelongsTo;

    public function getStripe(): ?array
    {
        if (! $this->stripe) {
            if ($this->stripe_id) {
                $this->stripe = Client::prices()->find($this->stripe_id);
            } else {
                $this->stripe = Client::prices()->first([
                    'metadata' => [
                        'type' => __CLASS__,
                        'id' => $this->id,
                    ],
                ]);
                if ($this->stripe) {
                    $this->update([
                        'stripe_id' => $this->stripe['id'],
                        'synced_to_stripe' => $this->name == $this->stripe['nickname'],
                    ]);
                }
            }
        }

        return $this->stripe;
    }

    public function stripeCreate(): array
    {
        if ($this->stripe_id) {
            throw new AlreadyCreated($this, 'price');
        }
        $this->getStripe();
        if (! $this->stripe) {
            if (! $this->product->stripe_id) {
                throw new NotYetCreatedProduct($this);
            }
            $this->stripe = Client::prices()->create([
                'product' => $this->product->stripe_id,
                'nickname' => $this->name,
                'currency' => 'HKD',
                'unit_amount' => $this->price,
                'metadata' => [
                    'type' => __CLASS__,
                    'id' => $this->id,
                ],
            ]);
            $this->update([
                'stripe_id' => $this->stripe['id'],
                'synced_to_stripe' => $this->name == $this->stripe['nickname'],
            ]);
        }

        return $this->stripe;
    }

    public function stripeUpdate(): array
    {
        if (! $this->stripe_id) {
            throw new NotYetCreated($this, 'price');
        }
        $this->stripe = Client::prices()->update(
            $this->stripe_id,
            ['nickname' => $this->name],
        );
        $this->update(['synced_to_stripe' => $this->name == $this->stripe['nickname']]);

        return $this->stripe;
    }
}
