<?php

namespace App\Library\Stripe;

class Checkout extends Base
{
    protected $prefix = 'checkouts/sessions';

    public function expire(string $id)
    {
        return $this->http->post("/{$this->prefix}/$id/expire")->throw()->json();
    }
}
