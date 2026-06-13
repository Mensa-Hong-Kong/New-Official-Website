<?php

namespace Tests\Feature\Library\Stripe\Webhooks;

use App\Library\Stripe\Events\Customer\Created;
use App\Library\Stripe\Jobs\CreateCustomer;
use App\Library\Stripe\Models\StripeCustomer;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CustomerDeletedTest extends TestCase
{
    use RefreshDatabase;

    protected function sign(int $timestamp, string $payload): string
    {
        return hash_hmac(
            'sha256',
            "$timestamp.$payload",
            config('stripe.keys.webhook')
        );
    }

    protected function signatureHeader(array $payload): array
    {
        $timestamp = time();
        $signature = $this->sign(
            time(),
            json_encode($payload)
        );

        return ['Stripe-Signature' => "t=$timestamp,v1=$signature"];
    }

    public function test_missing_data_object_id(): void
    {
        $data = ['type' => 'customer.deleted'];
        $response = $this->postJson(
            route('webhooks.stripe'),
            $data,
            $this->signatureHeader($data)
        );
        $response->assertInvalid(['data.object.id' => 'The data.object.id field is required.']);
    }

    public function test_customer_is_not_exists(): void
    {
        Queue::fake();
        $data = [
            'type' => 'customer.deleted',
            'data' => [
                'object' => ['id' => 'cus_NeGfPRiPKxeBi1'],
            ],
        ];
        $response = $this->postJson(
            route('webhooks.stripe'),
            $data,
            $this->signatureHeader($data)
        );
        $response->assertSuccessful();
        $response->assertSee('Webhook Handled');
        Queue::assertNothingPushed();
    }

    public function test_customer_exist_and_customer_is_user_and_user_is_not_exist(): void
    {
        Queue::fake();
        Event::fake(Created::class);
        StripeCustomer::createQuietly([
            'id' => 'cus_NeGfPRiPKxeBi1',
            'customerable_type' => User::class,
            'customerable_id' => 123,
        ]);
        $data = [
            'type' => 'customer.deleted',
            'data' => [
                'object' => ['id' => 'cus_NeGfPRiPKxeBi1'],
            ],
        ];
        $response = $this->postJson(
            route('webhooks.stripe'),
            $data,
            $this->signatureHeader($data)
        );
        $response->assertSee('Webhook Handled');
        $this->assertNull(StripeCustomer::find('cus_NeGfPRiPKxeBi1'));
        Queue::assertNothingPushed();
        Event::assertNotDispatched(Created::class);
    }


    public function test_customer_exist_and_customer_is_user_and_user_is_exists(): void
    {
        Event::fake(Created::class);
        Queue::fake();
        $user = User::factory()->createQuietly();
        $user->stripe()->createQuietly(['id' => 'cus_NeGfPRiPKxeBi1']);
        $data = [
            'type' => 'customer.deleted',
            'data' => [
                'object' => ['id' => 'cus_NeGfPRiPKxeBi1'],
            ],
        ];
        $response = $this->postJson(
            route('webhooks.stripe'),
            $data,
            $this->signatureHeader($data)
        );
        $response->assertSee('Webhook Handled');
        $this->assertNull(StripeCustomer::find('cus_NeGfPRiPKxeBi1'));
        $this->assertBroadcastChannel(
            Created::class,
            'App.Models.User.'.$user->id,
            PrivateChannel::class,
            ['created_stripe_customer' => false]
        );
        Queue::assertPushed(CreateCustomer::class);
    }
}
