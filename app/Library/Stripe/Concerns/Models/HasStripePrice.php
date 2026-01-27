<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Client;
use App\Library\Stripe\Exceptions\AlreadyCreatedPrice;
use App\Library\Stripe\Exceptions\NotYetCreated;
use App\Library\Stripe\Exceptions\NotYetCreatedProduct;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasStripePrice
{
    public array $stripeData = [
        'one_time' => null,
        'recurring' => null,
    ];

    protected string $interval; // day, week, month or year

    protected int $intervalCount; // number of intervals

    abstract public function product(): BelongsTo;

    public function getStripe(string $type): ?array
    {
        if (! $this->stripeData[$type]) {
            if ($this->{"stripe_{$type}_type_id"}) {
                $this->stripeData[$type] = Client::prices()->find($this->{"stripe_{$type}_type_id"});
            } else {
                $this->stripeData[$type] = Client::prices()->first([
                    'type' => $type,
                    'metadata' => [
                        'type' => __CLASS__,
                        'id' => $this->id,
                    ],
                ]);
                if ($this->stripeData[$type]) {
                    $this->update([
                        "stripe_{$type}_type_id" => $this->stripeData[$type]['id'],
                        "synced_{$type}_type_to_stripe" => $this->name == $this->stripeData[$type]['nickname'],
                    ]);
                }
            }
        }

        return $this->stripeData[$type];
    }

    public function stripeCreate($type): array
    {
        if ($this->{"stripe_{$type}_type_id"}) {
            throw new AlreadyCreatedPrice($this, $type);
        }
        $this->getStripe($type);
        if (! $this->stripeData[$type]) {
            if (! $this->product->stripe_id) {
                throw new NotYetCreatedProduct($this);
            }
            $data = [
                'product' => $this->product->stripe_id,
                'nickname' => $this->name,
                'type' => $type,
                'currency' => config('stripe.currency', 'hkd'),
                'unit_amount' => $this->value,
                'metadata' => [
                    'type' => __CLASS__,
                    'id' => $this->id,
                ],
            ];
            if ($type == 'recurring') {
                $data['recurring'] = [
                    'interval' => $this->interval,
                    'interval_count' => $this->intervalCount,
                ];
            }
            $this->stripeData[$type] = Client::prices()->create($data);
            $this->update([
                "stripe_{$type}_type_id" => $this->stripeData[$type]['id'],
                "synced_{$type}_type_to_stripe" => $this->name == $this->stripeData[$type]['nickname'],
            ]);
        }

        return $this->stripeData[$type];
    }

    public function stripeUpdate($type): array
    {
        if (! $this->{"stripe_{$type}_type_id"}) {
            throw new NotYetCreated($this, 'price');
        }
        $this->stripeData[$type] = Client::prices()->update(
            $this->{"stripe_{$type}_type_id"},
            ['nickname' => $this->name],
        );
        $this->update(["synced_{$type}_type_to_stripe" => $this->name == $this->stripeData[$type]['nickname']]);

        return $this->stripeData[$type];
    }

    public function stripeUpdateOrCreate($type): array
    {
        if (! $this->{"stripe_{$type}_type_id"}) {
            $this->stripeCreate($type);
        }
        if (! $this->{"synced_{$type}_type_to_stripe"}) {
            $this->stripeUpdate($type);
        }
        if (! $this->stripeData[$type]) {
            $this->getStripe($type);
        }

        return $this->stripeData[$type];
    }
}
