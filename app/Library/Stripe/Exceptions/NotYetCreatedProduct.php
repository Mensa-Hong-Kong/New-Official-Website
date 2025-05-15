<?php

namespace App\Library\Stripe\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class NotYetCreatedProduct extends Exception
{
    /**
     * Create a new CustomerAlreadyCreated instance.
     *
     * @return static
     */
    public function __construct(Model $owner)
    {
        parent::__construct('Product of '.class_basename($owner).' is not a Stripe product yet. See the stripeCreate method.');
    }
}
