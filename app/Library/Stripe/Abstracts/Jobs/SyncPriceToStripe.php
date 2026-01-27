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
        } elseif (
            (
                in_array('stripe_one_time_type_id', $model->getFillable()) &&
                ! $model->synced_one_time_type_to_stripe
            ) || (
                in_array('stripe_recurring_type_id', $model->getFillable()) &&
                ! $model->synced_recurring_type_to_stripe
            )
        ) {
            if (
                in_array('stripe_one_time_type_id', $model->getFillable()) &&
                ! $model->synced_one_time_type_to_stripe
            ) {
                $model->stripeUpdateOrCreate('one_time');
            }
            if (
                in_array('stripe_recurring_type_id', $model->getFillable()) &&
                ! $model->synced_recurring_type_to_stripe
            ) {
                $model->stripeUpdateOrCreate('recurring');
            }
            DB::commit();
        } else {
            DB::rollBack();
        }
    }
}
