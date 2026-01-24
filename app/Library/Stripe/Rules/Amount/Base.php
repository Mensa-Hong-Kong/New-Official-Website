<?php
 
namespace App\Library\Stripe\Rules\Amount;

use App\Library\Stripe\Amount;
use Illuminate\Validation\Rules\Numeric;
 
abstract class Base extends Numeric
{
    protected $priceDecimal;
    protected $maximum;
    protected $minimum;

    public function __construct()
    {
        $this->max(Amount::getMaximumValidation());
        if ($this->minimum) {
            $this->min($this->minimum);
        } else {
            $this->min(1 * 10**(-$this->priceDecimal));
        }
        if ($this->priceDecimal > 0) {
            $this->decimal(0, $this->priceDecimal);
        } else {
            $this->integer();
        }
    }
}