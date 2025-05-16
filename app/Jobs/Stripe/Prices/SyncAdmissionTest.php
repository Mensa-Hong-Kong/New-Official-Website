<?php

namespace App\Jobs\Stripe\Prices;

use App\Library\Stripe\Abstracts\Jobs\SyncPriceToStripe;
use App\Models\AdmissionTestPrice;

class SyncAdmissionTest extends SyncPriceToStripe
{
    protected string $model = AdmissionTestPrice::class;
}
