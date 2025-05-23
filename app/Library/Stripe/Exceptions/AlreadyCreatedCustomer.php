<?php

namespace App\Library\Stripe\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class AlreadyCreatedCustomer extends Exception
{
    /**
     * Create a new CustomerAlreadyCreated instance.
     *
     * @return static
     */
    public function __construct(Model $owner)
    {
        parent::__construct(class_basename($owner)." is already a Stripe customer with ID {$owner->stripe->id}.");
    }
}
