<?php

namespace App\Library\Stripe\Exceptions;

use Illuminate\Database\Eloquent\Model;
use Exception;

class AlreadyCreated extends Exception
{
    /**
     * Create a new CustomerAlreadyCreated instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $owner
     * @return static
     */
    public function __construct(Model $owner, string $type)
    {
        var_dump(class_basename($owner)." is already a Stripe $type with ID {$owner->stripe_id}.");
        parent::__construct(class_basename($owner)." is already a Stripe $type with ID {$owner->stripe_id}.");
    }
}
