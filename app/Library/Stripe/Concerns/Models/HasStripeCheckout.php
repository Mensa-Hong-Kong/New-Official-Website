<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Client;
use App\Library\Stripe\Exceptions\AlreadyCompleteCheckout;
use App\Library\Stripe\Exceptions\AlreadyCreated;
use App\Library\Stripe\Exceptions\NotYetCreated;
use Illuminate\Support\Facades\DB;

trait HasStripeCheckout
{
    /* 
     * checkout only can update meta date, so not use UpdatableBase,
     * and create method has extra parameters, so not use CreatableBase.
     */
    use Base;

    public function stripeItems($items): array
    {
        $return = [];
        foreach ($items as $priceID => $quantity) {
            $return[] = [
                'price' => $priceID,
                'quantity' => $quantity,
            ];
        }

        return $return;
    }

    public function stripeCreate(string $mode, array $items, array $options = []): array
    {
        if ($this->reference_number) {
            throw new AlreadyCreated($this, 'customer');
        }
        $data = [
            'customer' => $this->customer->stripe_id,
            'metadata' => [
                'type' => __CLASS__,
                'id' => $this->id,
            ],
            'mode' => $mode,
            'line_items' => $this->stripeItems($items),
            'expires_at' => $this->expired_at->timestamp,
            'locale' => config('services.stripe.locale', 'auto'),
            'payment_method_types' => config('services.stripe.paymentMethodTypes', ['cards']),
        ];
        $name = $this->customer->stripeName();
        $email = $this->customer->stripeEmail();
        if (! $this->customer->synced_to_stripe) {
            $data['customer_update'] = [
                'name' => $name,
                'email' => $email,
            ];
        }
        $data = array_merge($options, $data);
        $this->stripeData = Client::customers()->create($data);
        $this->update(['reference_number' => $this->stripeData['id']]);
        if (! $this->customer->synced_to_stripe) {
            $this->customer->update([
                'synced_to_stripe' => 
                    $this->stripeData['customer_details']['email'] == $name &&
                    $this->stripeData['customer_details']['email'] == $email
                ]
            );
        }

        return $this->stripeData;
    }

    public function getStripe(): ?array
    {
        if (! $this->reference_number) {
            return null;
        } else {
            $this->stripeData = Client::checkouts()->find($this->reference_number);
            
            return $this->stripeData;
        }
    }

    public function stripeExpire()
    {
        if (! $this->reference_number) {
            throw new NotYetCreated($this, 'expire');
        } else {
            $response = Client::checkouts()->expire($this->reference_number);
            switch ($response->status()) {
                case 200:
                    $this->stripe = $response->json();
                    break;
                case 400:
                    $data = $response->json();
                    switch ($data['error']['message']) {
                        case "Only Checkout Sessions with a status in [\"open\"] can be expired. This Checkout Session has a status of `expired`.":
                            $this->stripe = $this->getStripe();
                            break;
                        case "Only Checkout Sessions with a status in [\"open\"] can be expired. This Checkout Session has a status of `complete`.":
                            throw new AlreadyCompleteCheckout($this);
                        default:
                            $response->throw();
                    }
                default:
                    $response->throw();
            }
            $this->update([
                'expired_at' => now(),
                'status' => DB::raw("CASE status WHEN 'pending' THEN 'expired' ELSE status END"),
            ]);
        }

        return $this->stripe;
    }
}