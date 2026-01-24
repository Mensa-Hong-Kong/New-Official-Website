<?php

namespace App\Library\Stripe\Concerns\Models;

trait CreatableBase
{
    use Base;

    abstract public function stripeCreate(): array;
}
