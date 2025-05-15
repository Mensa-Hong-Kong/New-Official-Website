<?php

namespace App\Library\Stripe\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class NotYetCreated extends Exception
{
    /**
     * Create a new CustomerAlreadyCreated instance.
     *
     * @return static
     */
    public function __construct(Model $owner, $type)
    {
        parent::__construct(class_basename($owner)." is not a Stripe {$type} yet. See the stripeUpdate method.");
    }
}
