<?php

namespace App\Library\Stripe;

use Illuminate\Support\Facades\Http;

class Base
{
    const STRIPE_VERSION = '2025-04-30';

    protected $http;

    protected $prefix;

    public function __construct()
    {
        $this->http = Http::baseUrl('https://api.stripe.com/v1')
            ->withToken(config('service.stripe.keys.secret'))
            ->withHeader('Stripe-Version', static::STRIPE_VERSION.'.basil');
    }

    public function find(string $id): ?array
    {
        $response = $this->http->get(
            "/{$this->prefix}/$id",
        );
        if ($response->notFound()) {
            return null;
        }
        $response->throw();

        return $response->json();
    }

    public function create(array $data): array
    {
        return $this->http->post("/{$this->prefix}", $data)->throw()->json();
    }

    public function update(string $id, array $data): array
    {
        return $this->http->post("/{$this->prefix}/$id", $data)->throw()->json();
    }
}
