<?php

namespace App\Library\Stripe\Exceptions;

use Illuminate\Database\Eloquent\Model;
use Exception;

class NotYetCreated extends Exception
{
    /**
     * Create a new CustomerAlreadyCreated instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @return static
     */
    public function __construct(Model $owner, $type)
    {
        parent::__construct(class_basename($owner)." is not a Stripe {$type} yet. See the stripeUpdate method.");
    }
}
