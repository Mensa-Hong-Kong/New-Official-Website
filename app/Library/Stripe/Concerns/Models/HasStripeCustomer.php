<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Client;
use App\Library\Stripe\Exceptions\AlreadyCreatedCustomer;
use App\Models\StripeCustomer;

trait HasStripeCustomer
{
    public function stripe()
    {
        return $this->morphOne(StripeCustomer::class, 'customerable');
    }

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
        if ($this->stripe) {
            return $this->stripe->getStripe();
        } else {
            $result = Client::customers()->first([
                'metadata' => [
                    'type' => __CLASS__,
                    'id' => $this->id,
                ],
            ]);
            if ($result) {
                $this->update([
                    'synced_to_stripe' => $this->stripeName() == $result['name'] &&
                        $this->stripeEmail() == $result['email'],
                ]);
                $this->stripe = $this->stripe()->create(['id' => $result['id']]);
                $this->stripe->data = $result;
            }

            return $result;
        }
    }

    public function stripeCreate(): array
    {
        if ($this->stripe) {
            throw new AlreadyCreatedCustomer($this);
        }
        $result = $this->getStripe();
        if (! $result) {
            $result = Client::customers()->create([
                'name' => $this->stripeName(),
                'email' => $this->stripeEmail(),
                'metadata' => [
                    'type' => __CLASS__,
                    'id' => $this->id,
                ],
            ]);
            $this->update([
                'synced_to_stripe' => $this->stripeName() == $result['name'] &&
                    $this->stripeEmail() == $result['email'],
            ]);
            $this->stripe = $this->stripe()->create(['id' => $result['id']]);
        }
        $this->stripe->data = $result;

        return $result;
    }
}
