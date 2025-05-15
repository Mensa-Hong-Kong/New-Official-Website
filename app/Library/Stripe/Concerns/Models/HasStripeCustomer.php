<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Client;
use App\Library\Stripe\Exceptions\AlreadyCreated;

trait HasStripeCustomer
{
    use Base; // because creating checkout can update customer, so, non-updatable

    public function stripeName(): string
    {
        return $this->name;
    }

    public function stripeEmail(): ?string
    {
        return $this->email;
    }

    public function getStripe(): ?array
    {
        if (! $this->stripe) {
            if ($this->stripe_id) {
                $this->stripe = Client::customers()->find($this->stripe_id);
            } else {
                $this->stripe = Client::customers()->first([
                    'metadata' => [
                        'type' => __CLASS__,
                        'id' => $this->id,
                    ],
                ]);
                if ($this->stripe) {
                    $this->update([
                        'stripe_id' => $this->stripe['id'],
                        'synced_to_stripe' => $this->stripeName() == $this->stripe['name'] &&
                            $this->stripeEmail() == $this->stripe['email'],
                    ]);
                }
            }
        }

        return $this->stripe;
    }

    public function stripeCreate(): array
    {
        if ($this->stripe_id) {
            throw new AlreadyCreated($this, 'customer');
        }
        $name = $this->stripeName();
        $this->getStripe();
        if (! $this->stripe_id) {
            $this->stripe = Client::customers()->create([
                'name' => $name,
                'email' => $this->defaultEmail,
                'metadata' => [
                    'type' => __CLASS__,
                    'id' => $this->id,
                ],
            ]);
            $this->update([
                'stripe_id' => $this->stripe['id'],
                'synced_to_stripe' => $this->stripeName() == $this->stripe['name'] &&
                    $this->stripeEmail() == $this->stripe['email'],
            ]);
        }

        return $this->stripe;
    }
}
