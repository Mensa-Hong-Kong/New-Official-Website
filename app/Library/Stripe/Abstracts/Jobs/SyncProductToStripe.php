<?php

namespace App\Library\Stripe\Abstracts\Jobs;

use Illuminate\Support\Facades\DB;

abstract class SyncProductToStripe extends Base
{
    public function handle(): void
    {
        DB::beginTransaction();
        $model = $this->model::lockForUpdate()
            ->find($this->modelID);
        if (! $model->synced_to_stripe) {
            $model->stripeUpdateOrCreate();
            DB::commit();
        } else {
            DB::rollBack();
        }
    }
}
