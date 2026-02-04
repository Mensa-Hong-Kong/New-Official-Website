<?php

namespace App\Library\Stripe\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class AlreadyCreatedPrice extends Exception
{
    /**
     * Create a new CustomerAlreadyCreated instance.
     *
     * @return static
     */
    public function __construct(Model $owner, string $type)
    {
        $stripeID = $owner->{"stripe_{$type}_type_id"};
        $type = str_replace('_', ' ', $type);
        parent::__construct(class_basename($owner)." is already a Stripe $type type price with ID $stripeID.");
    }
}
