<?php

namespace Tests\Feature\Library\Stripe\Webhooks;

use App\Library\Stripe\Http\Middleware\Webhooks\VerifySignature;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;

class VerifySignatureTest extends TestCase
{
    protected Request $request;

    protected string|int $timestamp; // must be int, string just for testing fail case

    protected function setUp(): void
    {
        parent::setUp();

        config(['stripe.keys.webhook' => 'secret']);

        $this->request = new Request(content: 'Signed Body');
    }

    // string just for testing fail case
    public function withTimestamp(int|string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function withSignature(string $signature): void
    {
        $this->request->headers->set('Stripe-Signature', "t={$this->timestamp},v1=$signature");
    }

    private function sign(string $payload, string $secret): string
    {
        return hash_hmac('sha256', "{$this->timestamp}.$payload", $secret);
    }

    public function withSignedSignature(string $secret): void
    {
        $this->withSignature(
            $this->sign(
                $this->request->getContent(),
                $secret
            )
        );
    }

    public function test_response_is_received_when_secret_matches(): void
    {
        $this->withTimestamp(time());
        $this->withSignedSignature('secret');

        $response = (new VerifySignature)
            ->handle(
                $this->request,
                function ($request) {
                    return new Response('OK');
                }
            );

        $this->assertEquals('OK', $response->content());
    }

    public function test_response_is_received_when_timestamp_is_within_tolerance_zone(): void
    {
        $this->withTimestamp(time() - 300);
        $this->withSignedSignature('secret');

        $response = (new VerifySignature)
            ->handle(
                $this->request,
                function ($request) {
                    return new Response('OK');
                }
            );

        $this->assertEquals('OK', $response->content());
    }

    public function test_app_aborts_when_timestamp_is_too_old(): void
    {
        $this->withTimestamp(time() - 301);
        $this->withSignedSignature('secret');

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Timestamp outside the tolerance zone');

        (new VerifySignature)->handle(
            $this->request,
            function ($request) {}
        );
    }

    public function test_app_aborts_when_timestamp_is_invalid(): void
    {
        $this->withTimestamp('invalid');
        $this->withSignedSignature('secret');

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Unable to extract timestamp and signatures from header');

        (new VerifySignature)->handle(
            $this->request,
            function ($request) {}
        );
    }

    public function test_app_aborts_when_secret_does_not_match(): void
    {
        $this->withTimestamp(time());
        $this->withSignature('fail');

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('No signatures found matching the expected signature for payload');

        (new VerifySignature)->handle(
            $this->request,
            function ($request) {}
        );
    }

    public function test_app_aborts_when_no_secret_was_provided(): void
    {
        $this->withTimestamp(time());
        $this->withSignedSignature('');

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('No signatures found matching the expected signature for payload');

        (new VerifySignature)->handle(
            $this->request,
            function ($request) {}
        );
    }
}
