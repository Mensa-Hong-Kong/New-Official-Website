<?php

namespace App\Library\Stripe\Events\Customer;

use App\Library\Stripe\Models\StripeCustomer;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Created implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * @param  'created'|'deleted'  $action
     */
    public function __construct(
        public StripeCustomer $customer,
        public string $action,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel(str_replace('\\', '.', $this->customer->customerable_type).'.'.$this->customer->customerable_id)];
    }

    public function broadcastAs(): string
    {
        return 'StripeCustomerCreated';
    }

    public function broadcastWith(): array
    {
        return ['created_stripe_customer' => $this->action == 'created'];
    }
}
