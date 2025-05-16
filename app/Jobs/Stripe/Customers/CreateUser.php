<?php

namespace App\Jobs\Stripe\Customers;

use App\Library\Stripe\Abstracts\Jobs\CreateCustomer;
use App\Models\User;

class CreateUser extends CreateCustomer
{
    protected string $model = User::class;
}
