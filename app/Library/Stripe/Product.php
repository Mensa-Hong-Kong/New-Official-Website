<?php

namespace App\Library\Stripe;

use App\Library\Stripe\Concerns\HasSearch;

class Product extends Base
{
    use HasSearch;

    protected $prefix = 'products';
}
