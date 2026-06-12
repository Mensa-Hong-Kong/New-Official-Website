<?php

namespace App\Library\Stripe\Events\Customer;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class Synced implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Model|Collection $target,
        public bool $syncedToStripe,
    ) {}

    public function broadcastOn(): array
    {
        $return = [];
        if ($this->target instanceof Collection) {
            foreach ($this->target as $row) {
                $return[] = new PrivateChannel(str_replace('\\', '.', get_class($row)).'.'.$row->id);
            }

            return $return;
        } else {
            return [new PrivateChannel(str_replace('\\', '.', get_class($this->target)).'.'.$this->target->id)];
        }
    }

    public function broadcastAs(): string
    {
        return 'SyncedToStripe';
    }

    public function broadcastWith(): array
    {
        return ['synced_to_stripe' => $this->syncedToStripe];
    }
}
