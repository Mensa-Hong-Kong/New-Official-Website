<?php

namespace App\Library\Stripe\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class AlreadyCreated extends Exception
{
    /**
     * Create a new CustomerAlreadyCreated instance.
     *
     * @return static
     */
    public function __construct(Model $owner, string $type)
    {
        parent::__construct(class_basename($owner)." is already a Stripe $type with ID {$owner->stripe_id}.");
    }
}
