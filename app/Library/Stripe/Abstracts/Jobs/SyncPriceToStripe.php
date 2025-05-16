<?php

namespace App\Library\Stripe\Abstracts\Jobs;

use Illuminate\Support\Facades\DB;

abstract class SyncPriceToStripe extends Base
{
    public function handle(): void
    {
        DB::beginTransaction();
        $model = $this->model::lockForUpdate()
            ->find($this->modelID);
        if (! $model->product->stripe_id) {
            DB::rollBack();
            $this->release(60);
        } elseif (! $model->synced_to_stripe) {
            $model->stripeUpdateOrCreate();
            DB::commit();
        } else {
            DB::rollBack();
        }
    }
}
