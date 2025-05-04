<?php

namespace Tests\Feature\Schedules;

use App\Models\ContactHasVerification;
use App\Models\User;
use App\Models\UserHasContact;
use App\Schedules\CreateStripeUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateStripeUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_have_no_uncreated_user()
    {
        $this->expectNotToPerformAssertions();
        User::factory()->state(['stripe_id' => '123'])->create();
        (new CreateStripeUser)();
    }

    public function test_has_missing_stripe_id_user_searching_server_maintenance()
    {
        $user = User::factory()->state(['stripe_id' => null])->create();
        Http::fake(['https://api.stripe.com/v1/customers/search' => Http::response(status: 503)]);
        (new CreateStripeUser)();
        $this->assertFalse((bool) $user->refresh()->synced_to_stripe);
        $this->assertNull($user->refresh()->stripe_id);
    }

    public function test_has_created_stripe_user_missing_stripe_id_but_name_not_update_to_date()
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
        Http::fake([
            '*' => Http::response([
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
                        'name' => 'Jenny Rosen',
                        'next_invoice_sequence' => 1,
                        'phone' => null,
                        'preferred_locales' => [],
                        'shipping' => null,
                        'tax_exempt' => 'none',
                        'test_clock' => null,
                    ],
                ],
            ]),
        ]);
        (new CreateStripeUser)();
        $user = User::find($user->id);
        $this->assertFalse((bool) $user->synced_to_stripe);
        $this->assertEquals('cus_NeGfPRiPKxeBi1', $user->stripe_id);
    }

    public function test_has_created_stripe_user_missing_stripe_id_but_email_not_update_to_date()
    {
        $user = User::factory()->state(['synced_to_stripe' => false])->create();
        $name = [
            '0' => $user->given_name,
            '2' => $user->family_name,
        ];
        $contact = UserHasContact::factory()
            ->state([
                'user_id' => $user,
                'is_default' => true,
            ])->email()
            ->create();
        ContactHasVerification::create([
            'contact_id' => $contact->id,
            'contact' => $contact->contact,
            'type' => $contact->type,
            'verified_at' => now(),
            'creator_id' => $user->id,
            'creator_ip' => '127.0.0.1',
        ]);
        if ($user->middle_name) {
            $name['1'] = $user->middle_name;
        }
        ksort($name);
        Http::fake([
            '*' => Http::response([
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
                        'name' => implode(' ', $name),
                        'next_invoice_sequence' => 1,
                        'phone' => null,
                        'preferred_locales' => [],
                        'shipping' => null,
                        'tax_exempt' => 'none',
                        'test_clock' => null,
                    ],
                ],
            ]),
        ]);
        (new CreateStripeUser)();
        $user = User::find($user->id);
        $this->assertFalse((bool) $user->synced_to_stripe);
        $this->assertEquals('cus_NeGfPRiPKxeBi1', $user->stripe_id);
    }

    public function test_has_created_stripe_user_missing_stripe_id_all_update_to_date()
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
        Http::fake([
            '*' => Http::response([
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
                        'name' => implode(' ', $name),
                        'next_invoice_sequence' => 1,
                        'phone' => null,
                        'preferred_locales' => [],
                        'shipping' => null,
                        'tax_exempt' => 'none',
                        'test_clock' => null,
                    ],
                ],
            ]),
        ]);
        (new CreateStripeUser)();
        $user = User::find($user->id);
        $this->assertTrue((bool) $user->synced_to_stripe);
        $this->assertEquals('cus_NeGfPRiPKxeBi1', $user->stripe_id);
    }

    public function test_has_uncreated_stripe_user_creating_server_maintenance()
    {
        $user = User::factory()->state(['synced_to_stripe' => false])->create();
        Http::fake([
            '*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/customers/search',
                    'has_more' => false,
                    'data' => [],
                ])->pushStatus(503),
        ]);
        (new CreateStripeUser)();
        $this->assertFalse((bool) $user->refresh()->synced_to_stripe);
        $this->assertNull($user->refresh()->stripe_id);
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
        Http::fake([
            '*' => Http::sequence()
                ->push([
                    'object' => 'search_result',
                    'url' => '/v1/customers/search',
                    'has_more' => false,
                    'data' => [],
                ])->push([
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
                    'name' => implode(' ', $name),
                    'next_invoice_sequence' => 1,
                    'phone' => null,
                    'preferred_locales' => [],
                    'shipping' => null,
                    'tax_exempt' => 'none',
                    'test_clock' => null,
                ]),
        ]);
        (new CreateStripeUser)();
        $user = User::find($user->id);
        $this->assertTrue((bool) $user->synced_to_stripe);
        $this->assertEquals('cus_NffrFeUfNV2Hib', $user->stripe_id);
    }
}
