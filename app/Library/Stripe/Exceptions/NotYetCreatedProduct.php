<?php

namespace App\Library\Stripe\Exceptions;

use Illuminate\Database\Eloquent\Model;
use Exception;

class NotYetCreatedProduct extends Exception
{
    /**
     * Create a new CustomerAlreadyCreated instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @return static
     */
    public function __construct(Model $owner)
    {
        parent::__construct('Product of '.class_basename($owner)." is not a Stripe product yet. See the stripeCreate method.");
    }
}
