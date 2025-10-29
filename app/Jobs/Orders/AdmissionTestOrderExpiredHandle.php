<?php

namespace App\Jobs\Orders;

use App\Abstracts\Jobs\Order;
use App\Models\AdmissionTestOrder;
use Illuminate\Support\Facades\DB;

class AdmissionTestOrderExpiredHandle extends Order
{
    public function handle(): void
    {
        DB::beginTransaction();
        $order = AdmissionTestOrder::lockForUpdate()->find($this->modelID);
        if ($order->expired_at > now()) {
            $this->release($order->expired_at);
        } else {
            if ($order->status == 'pending') {
                $order->update(['status' => 'expired']);
            }
            if ($order->status != 'succeeded') {
                $order->tests()->detach();
            }
        }
    }
}
