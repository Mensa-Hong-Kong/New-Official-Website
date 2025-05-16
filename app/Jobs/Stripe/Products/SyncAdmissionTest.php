<?php

namespace App\Jobs\Stripe\Products;

use App\Library\Stripe\Abstracts\Jobs\SyncProductToStripe;
use App\Models\AdmissionTestProduct;

class SyncAdmissionTest extends SyncProductToStripe
{
    protected string $model = AdmissionTestProduct::class;
}
