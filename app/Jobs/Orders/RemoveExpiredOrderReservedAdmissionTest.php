<?php

namespace App\Jobs\Orders;

use App\Abstracts\Jobs\Order;
use App\Models\AdmissionTestOrder;

class RemoveExpiredOrderReservedAdmissionTest extends Order
{
    public function handle(): void
    {
        $order = AdmissionTestOrder::find($this->modelID);
        if ($order->expired_at > now()) {
            $this->release($order->expired_at);
        } elseif ($order->status == 'pending') {
            $order->tests()->detach();
            $order->update(['status' => 'expired']);
        }
    }
}
