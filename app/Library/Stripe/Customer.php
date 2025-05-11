<?php

namespace App\Library\Stripe;

use App\Library\Stripe\Concerns\HasSearch;

class Customer extends Base
{
    use HasSearch;

    protected $prefix = 'customers';
}
