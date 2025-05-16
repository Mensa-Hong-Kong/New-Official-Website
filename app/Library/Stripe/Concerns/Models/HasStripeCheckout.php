<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Client;
use App\Library\Stripe\Exceptions\AlreadyCreated;
use App\Library\Stripe\Exceptions\NotYetCreated;
use Illuminate\Database\Eloquent\Model;

trait HasStripeCheckout
{
    use Base; // because checkout only can update meta date, so, non-updatable

    public function stripeItems(): array
    {
        $return = [];
        foreach ($this->items as $item) {
            $return[] = [
                'price' => $item->stripe_id,
                'quantity' => $item->quantity,
            ];
        }

        return $return;
    }

    public function stripeCreate(Model $customer, string $mode, array $options): array
    {
        if ($this->stripe_id) {
            throw new AlreadyCreated($this, 'customer');
        }
        $data = [
            'customer' => $customer->stripe_id,
            'metadata' => [
                'type' => __CLASS__,
                'id' => $this->id,
            ],
            'mode' => $mode,
            'line_items' => [],
        ];
        $data['line_items'] = $this->stripeItems();
        $name = $customer->stripeName();
        $email = $customer->stripeEmail();
        if ($customer->synced_to_stripe) {
            $data['customer_update'] = [
                'name' => $name,
                'email' => $email,
            ];
        }
        if (! isset($options['locale'])) {
            $data['locale'] = config('stripe.locale');
        }
        $data = array_merge($options, $data);
        $this->stripe = Client::customers()->create($data);
        $this->update(['stripe_id' => $this->stripe['id']]);
        $customer->update(['synced_to_stripe' => true]);

        return $this->stripe;
    }

    public function getStripe(): ?array
    {
        if (! $this->stripe) {
            if (! $this->stripe_id) {
                throw new NotYetCreated($this, 'checkout');
            } else {
                $this->stripe = Client::checkouts()->find($this->stripe_id);
            }
        }

        return $this->stripe;
    }
}
