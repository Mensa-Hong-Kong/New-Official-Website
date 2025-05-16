<?php

namespace App\Library\Stripe\Abstracts\Jobs;

use Illuminate\Support\Facades\DB;

abstract class CreateCustomer extends Base
{
    public function handle(): void
    {
        DB::beginTransaction();
        $model = $this->model::lockForUpdate()
            ->find($this->modelID);
        if (! $model->stripe_id) {
            $model->stripeCreate();
            DB::commit();
        } else {
            DB::rollBack();
        }
    }
}
