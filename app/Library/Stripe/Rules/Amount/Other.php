<?php
 
namespace App\Library\Stripe\Rules\Amount;

use App\Library\Stripe\Amount;

class Other extends Base
{
    public function __construct()
    {
        $this->priceDecimal = Amount::getValidationDecimal();
        parent::__construct();
    }
}