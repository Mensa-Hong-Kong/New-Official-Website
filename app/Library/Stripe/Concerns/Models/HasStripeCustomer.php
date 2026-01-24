<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Client;
use App\Library\Stripe\Exceptions\AlreadyCreatedCustomer;
use App\Library\Stripe\Exceptions\NotYetCreated;
use App\Library\Stripe\Models\StripeCustomer;

trait HasStripeCustomer
{
    use CreatableBase;

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
        if ($this->stripeData) {
            return $this->stripeData;
        } elseif ($this->stripe) {
            $this->stripeData = Client::customers()->find($this->stripe->id);

            return $this->stripeData;
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
                $this->stripeData = $result;
            }

            return $result;
        }
    }

    public function stripeCreate(): array
    {
        if ($this->stripe) {
            throw new AlreadyCreatedCustomer($this);
        }
        $this->stripeData = $this->getStripe();
        if (! $this->stripeData) {
            $this->stripeData = Client::customers()->create([
                'name' => $this->stripeName(),
                'email' => $this->stripeEmail(),
                'metadata' => [
                    'type' => __CLASS__,
                    'id' => $this->id,
                ],
            ]);
            $this->update([
                'synced_to_stripe' => $this->stripeName() == $this->stripeData['name'] &&
                    $this->stripeEmail() == $this->stripeData['email'],
            ]);
            $this->stripe = $this->stripe()->create(['id' => $this->stripeData['id']]);
        }

        return $this->stripeData;
    }
}
