<?php

namespace App\Library\Stripe;

use App\Library\Stripe\Concerns\HasSearch;

class Checkout extends Base
{
    protected $prefix = 'checkouts/sessions';

    public function expire(string $id)
    {
        return $this->http->post("/{$this->prefix}/$id/expire")->throw()->json();
    }
}
