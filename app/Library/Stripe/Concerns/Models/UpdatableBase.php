<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Exceptions\NotYetCreated;

trait UpdatableBase
{
    use Base;

    public abstract function stripeUpdate(): array;

    public function stripeUpdateOrCreate(): array
    {
        if(! $this->stripe_id) {
            $this->stripe = $this->stripeCreate();
        }
        if(! $this->synced_to_stripe) {
            $this->stripe = $this->stripeUpdate();
        }
        if(! $this->stripe) {
            $this->getStripe();
        }

        return $this->stripe;
    }
}
