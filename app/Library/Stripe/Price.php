<?php

namespace App\Library\Stripe;

use App\Library\Stripe\Concerns\HasSearch;

class Price extends Base
{
    use HasSearch;

    protected $prefix = 'prices';
}
