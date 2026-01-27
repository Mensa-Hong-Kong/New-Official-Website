<?php

namespace App\Library\Stripe\Concerns\Models;

trait Recurrable
{
    use HasStripePrice;

    public function getRecurringTypeStripe(): ?array
    {
        return $this->getStripe('recurring');
    }

    public function createRecurringTypeStripe(): array
    {
        return $this->stripeCreate('recurring');
    }

    public function updateRecurringTypeStripe(): array
    {
        return $this->stripeUpdate('recurring');
    }

    public function updateOrCreateRecurringTypeStripe(): array
    {
        return $this->stripeUpdateOrCreate('recurring');
    }
}
