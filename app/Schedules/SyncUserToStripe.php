<?php

namespace App\Schedules;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class SyncUserToStripe
{
    public function __invoke()
    {
        $users = User::with(['defaultEmail'])
            ->where('synced_to_stripe', false)
            ->get();
        $http = Http::baseUrl('https://api.stripe.com/v1')
            ->withToken(config('services.stripe.keys.secret'));
        foreach ($users as $user) {
            $name = [
                '0' => $user->given_name,
                '2' => $user->family_name,
            ];
            if ($user->middle_name) {
                $name['1'] = $user->middle_name;
            }
            ksort($name);
            $name = implode(' ', $name);
            if (! $user->stripe_id) {
                $response = $http->get(
                    '/customers/search',
                    ['query' => "metadata['type']:'user' AND metadata['id']:'{$user->id}'"]
                );
                if (! $response->ok()) {
                    continue;
                } elseif (count($response->json('data'))) {
                    $user->update(['stripe_id' => $response->json('data')[0]['id']]);
                } else {
                    $response = $http->post(
                        '/customers',
                        [
                            'name' => $name,
                            'email' => $user->defaultEmail,
                            'metadata' => [
                                'type' => 'user',
                                'id' => $user->id,
                            ],
                        ]
                    );
                    if ($response->ok() && $response->json('id')) {
                        $user->update([
                            'stripe_id' => $response->json('id'),
                            'synced_to_stripe' => true,
                        ]);
                    }

                    continue;
                }
            }
            $response = $http->put(
                "/customers/{$user->stripe_id}",
                [
                    'name' => $name,
                    'email' => $user->defaultEmail,
                ]
            );
            if (
                $response->ok() &&
                $response->json('name') == $name &&
                $response->json('email') == $user->defaultEmail
            ) {
                $user->update(['synced_to_stripe' => true]);
            }
        }
    }
}
