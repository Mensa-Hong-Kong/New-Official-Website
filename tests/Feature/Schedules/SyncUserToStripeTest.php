<?php

namespace Tests\Feature\Schedules;

use App\Models\User;
use App\Schedules\SyncUserToStripe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncUserToStripeTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_unsynced_user()
    {
        $this->expectNotToPerformAssertions();
        User::factory()->state(['synced_to_stripe' => true])->create();
        (new SyncUserToStripe)();
    }

    public function test_has_missing_stripe_id_user_searching_server_maintenance()
    {
        $user = User::factory()->state(['synced_to_stripe' => false])->create();
        Http::fake(['https://api.stripe.com/v1/customers/search' => Http::response(status: 503)]);
        (new SyncUserToStripe)();
        $this->assertFalse((bool) $user->refresh()->synced_to_stripe);
    }

    public function test_has_uncreated_stripe_user_creating_server_maintenance()
    {
        $user = User::factory()->state(['synced_to_stripe' => false])->create();
        Http::fake([
            'https://api.stripe.com/v1/customers/search' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/customers/search',
                'has_more' => false,
                'data' => [],
            ]),
            'https://api.stripe.com/v1/customers' => Http::response(status: 503),
        ]);
        (new SyncUserToStripe)();
        $this->assertFalse((bool) $user->refresh()->synced_to_stripe);
    }

    public function test_has_uncreated_stripe_user_create_success()
    {
        $user = User::factory()->state(['synced_to_stripe' => false])->create();
        $name = [
            '0' => $user->given_name,
            '2' => $user->family_name,
        ];
        if ($user->middle_name) {
            $name['1'] = $user->middle_name;
        }
        ksort($name);
        $name = implode(' ', $name);
        Http::fake([
            'https://api.stripe.com/v1/customers/search' => Http::response([
                'object' => 'search_result',
                'url' => '/v1/customers/search',
                'has_more' => false,
                'data' => [],
            ]),
            'https://api.stripe.com/v1/customers' => Http::response([
                'id' => 'cus_NffrFeUfNV2Hib',
                'object' => 'customer',
                'address' => null,
                'balance' => 0,
                'created' => now()->timestamp,
                'currency' => null,
                'default_source' => null,
                'delinquent' => false,
                'description' => null,
                'email' => 'jennyrosen@example.com',
                'invoice_prefix' => '0759376C',
                'invoice_settings' => [
                    'custom_fields' => null,
                    'default_payment_method' => null,
                    'footer' => null,
                    'rendering_options' => null,
                ],
                'livemode' => false,
                'metadata' => [
                    'type' => 'user',
                    'id' => $user->id,
                ],
                'name' => $name,
                'next_invoice_sequence' => 1,
                'phone' => null,
                'preferred_locales' => [],
                'shipping' => null,
                'tax_exempt' => 'none',
                'test_clock' => null,
            ]),
        ]);
        (new SyncUserToStripe)();
        $this->assertTrue((bool) $user->refresh()->synced_to_stripe);
    }

    public function test_has_created_stripe_user_missing_stripe_id_update_fail()
    {
        $user = User::factory()->state(['synced_to_stripe' => false])->create();
        $name = [
            '0' => $user->given_name,
            '2' => $user->family_name,
        ];
        if ($user->middle_name) {
            $name['1'] = $user->middle_name;
        }
        ksort($name);
        $name = implode(' ', $name);
        Http::fake([
            '*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/customers/search',
                    'has_more' => false,
                    'data' => [
                        [
                            'id' => 'cus_NeGfPRiPKxeBi1',
                            'object' => 'customer',
                            'address' => null,
                            'balance' => 0,
                            'created' => now()->timestamp,
                            'currency' => null,
                            'default_source' => null,
                            'delinquent' => false,
                            'description' => null,
                            'email' => null,
                            'invoice_prefix' => '47D37F8F',
                            'invoice_settings' => [
                                'custom_fields' => null,
                                'default_payment_method' => 'pm_1Msy7wLkdIwHu7ixsxmFvcz7',
                                'footer' => null,
                                'rendering_options' => null,
                            ],
                            'livemode' => false,
                            'metadata' => [
                                'type' => 'user',
                                'id' => $user->id,
                            ],
                            'name' => $name,
                            'next_invoice_sequence' => 1,
                            'phone' => null,
                            'preferred_locales' => [],
                            'shipping' => null,
                            'tax_exempt' => 'none',
                            'test_clock' => null,
                        ],
                    ],
                ])->pushStatus(503),
        ]);
        (new SyncUserToStripe)();
        $user = User::find($user->id);
        $this->assertNull($user->stripe_id);
        $this->assertFalse((bool) $user->synced_to_stripe);
    }

    public function test_has_created_stripe_user_missing_stripe_id_that_update_success()
    {
        $user = User::factory()
            ->state(['synced_to_stripe' => false])->create();
        $name = [
            '0' => $user->given_name,
            '2' => $user->family_name,
        ];
        if ($user->middle_name) {
            $name['1'] = $user->middle_name;
        }
        ksort($name);
        $name = implode(' ', $name);
        Http::fake([
            '*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/customers/search',
                    'has_more' => false,
                    'data' => [
                        [
                            'id' => 'cus_NeGfPRiPKxeBi1',
                            'object' => 'customer',
                            'address' => null,
                            'balance' => 0,
                            'created' => now()->timestamp,
                            'currency' => null,
                            'default_source' => null,
                            'delinquent' => false,
                            'description' => null,
                            'email' => null,
                            'invoice_prefix' => '47D37F8F',
                            'invoice_settings' => [
                                'custom_fields' => null,
                                'default_payment_method' => null,
                                'footer' => null,
                                'rendering_options' => null,
                            ],
                            'livemode' => false,
                            'metadata' => [
                                'type' => 'user',
                                'id' => $user->id,
                            ],
                            'name' => $name,
                            'next_invoice_sequence' => 1,
                            'phone' => null,
                            'preferred_locales' => [],
                            'shipping' => null,
                            'tax_exempt' => 'none',
                            'test_clock' => null,
                        ],
                    ],
                ])->push([
                    'id' => 'cus_NeGfPRiPKxeBi1',
                    'object' => 'customer',
                    'address' => null,
                    'balance' => 0,
                    'created' => now()->timestamp,
                    'currency' => null,
                    'default_source' => null,
                    'delinquent' => false,
                    'description' => null,
                    'email' => null,
                    'invoice_prefix' => '0759376C',
                    'invoice_settings' => [
                        'custom_fields' => null,
                        'default_payment_method' => null,
                        'footer' => null,
                        'rendering_options' => null,
                    ],
                    'livemode' => false,
                    'metadata' => [
                        'type' => 'user',
                        'id' => $user->id,
                    ],
                    'name' => $name,
                    'next_invoice_sequence' => 1,
                    'phone' => null,
                    'preferred_locales' => [],
                    'shipping' => null,
                    'tax_exempt' => 'none',
                    'test_clock' => null,
                ]),
        ]);
        (new SyncUserToStripe)();
        $user = User::find($user->id);
        $this->assertEquals('cus_NeGfPRiPKxeBi1', $user->stripe_id);
        $this->assertTrue((bool) $user->synced_to_stripe);
    }

    public function test_has_outdate_stripe_user_update_success()
    {
        $user = User::factory()
            ->state([
                'stripe_id' => 'cus_NeGfPRiPKxeBi1',
                'synced_to_stripe' => false,
            ])->create();
        $name = [
            '0' => $user->given_name,
            '2' => $user->family_name,
        ];
        if ($user->middle_name) {
            $name['1'] = $user->middle_name;
        }
        ksort($name);
        $name = implode(' ', $name);
        Http::fake([
            '*' => Http::response([
                'id' => 'cus_NeGfPRiPKxeBi1',
                'object' => 'customer',
                'address' => null,
                'balance' => 0,
                'created' => now()->timestamp,
                'currency' => null,
                'default_source' => null,
                'delinquent' => false,
                'description' => null,
                'email' => null,
                'invoice_prefix' => '0759376C',
                'invoice_settings' => [
                    'custom_fields' => null,
                    'default_payment_method' => null,
                    'footer' => null,
                    'rendering_options' => null,
                ],
                'livemode' => false,
                'metadata' => [
                    'type' => 'user',
                    'id' => $user->id,
                ],
                'name' => $name,
                'next_invoice_sequence' => 1,
                'phone' => null,
                'preferred_locales' => [],
                'shipping' => null,
                'tax_exempt' => 'none',
                'test_clock' => null,
            ]),
        ]);
        (new SyncUserToStripe)();
        $user = User::find($user->id);
        $this->assertTrue((bool) $user->synced_to_stripe);
    }
}
