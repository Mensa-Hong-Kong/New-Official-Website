<?php

namespace App\Library\Stripe\Concerns\Models;

trait UpdatableBase
{
    use CreatableBase;

    abstract public function stripeUpdate(): array;

    public function stripeUpdateOrCreate(): array
    {
        if (! $this->stripe_id) {
            $this->stripeData = $this->stripeCreate();
        }
        if (! $this->synced_to_stripe) {
            $this->stripeData = $this->stripeUpdate();
        }
        if (! $this->stripeData) {
            $this->getStripe();
        }

        return $this->stripeData;
    }
}
