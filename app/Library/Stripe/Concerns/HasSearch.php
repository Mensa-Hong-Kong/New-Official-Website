<?php

namespace App\Library\Stripe\Concerns;

trait HasSearch
{
    protected $http;

    public function search(array $query)
    {
        $data = [];
        foreach ($query as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $data[] = "{$key}['$subKey']:'$subValue'";
                }
            } else {
                $data[] = "$key:'$value'";
            }
        }
        $data = implode(' AND ', $data);
        $response = $this->http->get(
            "/{$this->prefix}/search",
            ['query' => $data]
        )->throw();
        if (count($response->json('data'))) {
            return $response->json('data');
        }

        return null;
    }

    public function first($query)
    {
        return $this->search($query)[0] ?? null;
    }
}
