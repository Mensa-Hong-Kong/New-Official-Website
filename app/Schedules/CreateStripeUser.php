<?php

namespace App\Schedules;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CreateStripeUser
{
    public function __invoke()
    {
        $userIDs = User::whereNull('stripe_id')
            ->get('id')
            ->pluck('id')
            ->toArray();
        $http = Http::baseUrl('https://api.stripe.com/v1')
            ->withToken(config('services.stripe.keys.secret'));
        foreach ($userIDs as $userID) {
            DB::beginTransaction();
            $user = User::with('defaultEmail')
                ->lockForUpdate()
                ->find($userID);
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
                    DB::rollBack();
                } elseif (count($response->json('data'))) {
                    $user->update([
                        'stripe_id' => $response->json('data')[0]['id'],
                        'synced_to_stripe' => $name == $response->json('data')[0]['name'] &&
                            $user->defaultEmail == $response->json('data')[0]['email'],
                    ]);
                    DB::commit();
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
                        DB::commit();
                    } else {
                        DB::rollBack();
                    }
                }
            }
        }
    }
}
