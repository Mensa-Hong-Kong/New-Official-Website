<?php

namespace App\Library\Stripe\Abstracts\Jobs;

use Illuminate\Support\Facades\DB;

abstract class UpdateCheckoutWhenExpired extends Base
{
    public function handle(): void
    {
        DB::beginTransaction();
        $model = $this->model::lockForUpdate()
            ->find($this->modelID);
        $result = $model->getStripe();
        if ($result->payment_status == 'succeeded') {
            $model->update(['statue' => 'succeeded']);
            DB::commit();
        } elseif ($result->status == 'expired') {
            $model->update(['statue' => 'expired']);
            DB::commit();
        } else {
            DB::rollBack();
            $this->release(60);
        }
    }
}
