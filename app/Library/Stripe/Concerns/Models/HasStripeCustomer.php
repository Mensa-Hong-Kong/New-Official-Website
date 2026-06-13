<?php

namespace App\Library\Stripe\Concerns\Models;

use App\Library\Stripe\Client;
use App\Library\Stripe\Events\Customer\Synced;
use App\Library\Stripe\Exceptions\AlreadyCreatedCustomer;
use App\Library\Stripe\Jobs\CreateCustomer;
use App\Library\Stripe\Models\StripeCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait HasStripeCustomer
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $synced_to_stripe
 * @property-read StripeCustomer|null $stripe
 *
 * @mixin Model
 *
 * @method static void created(\Closure|string $callback)
 * @method static void updated(\Closure|string $callback)
 */
trait HasStripeCustomer
{
    use CreatableBase;

    public static function bootHasStripeCustomer()
    {
        static::created(
            function (Model $customer): void {
                CreateCustomer::dispatch(
                    __CLASS__,
                    $customer->id
                );
            }
        );
        static::updated(
            function (Model $customer) {
                if ($customer->wasChanged('synced_to_stripe')) {
                    event(new Synced($customer, $customer->synced_to_stripe));
                }
            }
        );
    }

    public function stripe(): MorphOne
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
